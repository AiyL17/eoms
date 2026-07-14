<?php

namespace App\Http\Controllers;

use App\Models\EoActivityLog;
use App\Models\ExecutiveOrder;
use App\Models\User;
use App\Notifications\EoDeleted;
use App\Notifications\EoUploaded;
use App\Notifications\EoStatusChanged;
use App\Notifications\EoUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExecutiveOrderController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = ExecutiveOrder::with('uploader')->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('year')) {
            $query->byYear((int) $request->year);
        }

        // ── Sorting ───────────────────────────────────────────────────────────
        $sortable = ['eo_number', 'date_issued', 'signed_by', 'status', 'year'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';

        if ($sort) {
            $query->reorder()->orderBy($sort, $dir);
        }

        $orders = $query->paginate(15)->withQueryString();

        $years = ExecutiveOrder::selectRaw('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $statuses = ExecutiveOrder::statuses();

        return view('executive-orders.index', compact('orders', 'years', 'statuses', 'sort', 'dir'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create()
    {
        $currentYear     = (int) date('Y');
        $nextItemNumber  = ExecutiveOrder::nextItemNumber($currentYear);
        $amendableOrders = ExecutiveOrder::where('status', 'active')
            ->orderBy('year', 'desc')
            ->orderBy('item_number', 'desc')
            ->get(['id', 'eo_number', 'title']);

        return view('executive-orders.create', compact('currentYear', 'nextItemNumber', 'amendableOrders'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_number'     => [
                'required', 'integer', 'min:1',
                Rule::unique('executive_orders')->where(fn ($q) => $q->where('year', $request->year)),
            ],
            'year'            => 'required|integer|min:2000|max:2100',
            'title'           => 'required|string|max:500',
            'subject'         => 'required|string|max:500',
            'content_summary' => 'nullable|string|max:5000',
            'date_issued'     => 'required|date',
            'date_effective'  => 'nullable|date',
            'signed_by'       => 'required|string|max:255',
            'pdf_file'        => 'required|file|mimes:pdf|max:20480',
            'status'          => 'required|in:active,amended,repealed,suspended,superseded,under_review',
            'status_notes'    => 'nullable|string|max:1000',
            'amends_id'       => 'nullable|exists:executive_orders,id',
            'tags'            => 'nullable|string',
            'signature_data'  => ['nullable', 'string', 'max:200000', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/]+=*$/'],
        ], [
            'item_number.unique' => 'An executive order with this item number already exists for the selected year.',
            'pdf_file.max'       => 'The PDF file must not exceed 20 MB.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // Store the PDF
            $file             = $request->file('pdf_file');
            $originalFilename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $file->getClientOriginalName());
            $storedPath       = $file->store('executive-orders', 'local');

            // Parse tags
            $tags = null;
            if (! empty($validated['tags'])) {
                $tags = array_map('trim', explode(',', $validated['tags']));
                $tags = array_filter($tags);
                $tags = array_values($tags);
            }

            // Build EO number
            $eoNumber = ExecutiveOrder::buildEoNumber($validated['item_number'], $validated['year']);

            $eo = ExecutiveOrder::create([
                'eo_number'       => $eoNumber,
                'item_number'     => $validated['item_number'],
                'year'            => $validated['year'],
                'title'           => $validated['title'],
                'subject'         => $validated['subject'],
                'content_summary' => $validated['content_summary'] ?? null,
                'date_issued'     => $validated['date_issued'],
                'date_effective'  => $validated['date_effective'] ?? null,
                'signed_by'       => $validated['signed_by'],
                'pdf_path'        => $storedPath,
                'original_filename' => $originalFilename,
                'file_size'       => $file->getSize(),
                'status'          => $validated['status'],
                'status_notes'    => $validated['status_notes'] ?? null,
                'amends_id'       => $validated['amends_id'] ?? null,
                'tags'            => $tags,
                'signature_data'  => $validated['signature_data'] ?? null,
                'uploaded_by'     => auth()->id(),
            ]);

            // If user has no profile signature yet and drew one here, save it to their profile
            $sigValue = $validated['signature_data'] ?? null;
            if ($sigValue && ! auth()->user()->signature_data) {
                auth()->user()->update(['signature_data' => $sigValue]);
            }

            // If this EO amends another, update the original EO's status
            if (! empty($validated['amends_id'])) {
                $original = ExecutiveOrder::find($validated['amends_id']);
                if ($original) {
                    $originalOldStatus = $original->status;
                    $original->update([
                        'status'        => 'amended',
                        'status_notes'  => "Amended by {$eoNumber}",
                        'amended_by_id' => $eo->id,
                        'updated_by'    => auth()->id(),
                    ]);
                    EoActivityLog::record($original, 'status_changed', ['status' => $originalOldStatus], ['status' => 'amended'], "Amended by {$eoNumber}");

                    // Fix 2: Notify the original EO's uploader that their EO was automatically
                    // set to 'amended' as a result of this new EO being uploaded.
                    $originalUploader = $original->uploader;
                    if ($originalUploader && $originalUploader->id !== auth()->id()) {
                        $originalUploader->notify(new EoStatusChanged($original, $originalOldStatus, 'amended', auth()->user()));
                    }
                }
            }

            // Log the creation
            EoActivityLog::record($eo, 'created', null, [
                'eo_number' => $eoNumber,
                'title'     => $eo->title,
                'status'    => $eo->status,
            ]);

            // Notify all admins that a new EO was uploaded.
            // This applies whether the uploader is a staff member or another admin
            // (admins other than the one who uploaded still need to be informed).
            $uploader = auth()->user();
            $admins = User::where('role', 'admin')->where('id', '!=', $uploader->id)->get();
            foreach ($admins as $admin) {
                $admin->notify(new EoUploaded($eo, $uploader));
            }
        });

        return redirect()
            ->route('executive-orders.index')
            ->with('success', 'Executive Order uploaded successfully.');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(ExecutiveOrder $executiveOrder)
    {
        $executiveOrder->load(['uploader', 'updater', 'amends', 'amendedBy', 'activityLogs.user']);
        return view('executive-orders.show', ['eo' => $executiveOrder]);
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(ExecutiveOrder $executiveOrder)
    {
        $statuses = ExecutiveOrder::statuses();
        return view('executive-orders.edit', ['eo' => $executiveOrder, 'statuses' => $statuses]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, ExecutiveOrder $executiveOrder)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:500',
            'subject'         => 'required|string|max:500',
            'content_summary' => 'nullable|string|max:5000',
            'date_issued'     => 'required|date',
            'date_effective'  => 'nullable|date',
            'signed_by'       => 'required|string|max:255',
            'status'          => 'required|in:active,amended,repealed,suspended,superseded,under_review',
            'status_notes'    => 'nullable|string|max:1000',
            'tags'            => 'nullable|string',
            'pdf_file'        => 'nullable|file|mimes:pdf|max:20480',
            'signature_data'  => ['nullable', 'string', 'max:200000', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/]+=*$/'],
            'log_notes'       => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $validated, $executiveOrder) {
            $oldValues = $executiveOrder->only(['title', 'subject', 'status', 'date_issued', 'date_effective', 'signed_by']);
            $oldStatus = $executiveOrder->status;

            // Handle PDF replacement
            if ($request->hasFile('pdf_file')) {
                // Delete old file
                Storage::disk('local')->delete($executiveOrder->pdf_path);
                $file = $request->file('pdf_file');
                $validated['pdf_path']          = $file->store('executive-orders', 'local');
                $validated['original_filename'] = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $file->getClientOriginalName());
                $validated['file_size']          = $file->getSize();
            }

            // Parse tags
            if (isset($validated['tags'])) {
                $tags = array_filter(array_map('trim', explode(',', $validated['tags'])));
                $validated['tags'] = array_values($tags) ?: null;
            }

            // Handle signature: keep existing if blank, clear if 'CLEAR', otherwise save new
            if (empty($validated['signature_data'])) {
                unset($validated['signature_data']); // keep existing
            } elseif ($validated['signature_data'] === 'CLEAR') {
                $validated['signature_data'] = null; // explicitly cleared
            }
            // else: new base64 PNG — save as-is

            // If user has no profile signature yet and drew one here, save it to their profile
            $newSig = $validated['signature_data'] ?? null;
            if ($newSig && $newSig !== 'CLEAR' && ! auth()->user()->signature_data) {
                auth()->user()->update(['signature_data' => $newSig]);
            }

            $validated['updated_by'] = auth()->id();
            unset($validated['log_notes']); // audit-only field, not a model column
            $executiveOrder->update($validated);

            $newValues = $executiveOrder->only(['title', 'subject', 'status', 'date_issued', 'date_effective', 'signed_by']);

            // Log status change separately if status changed
            if ($oldStatus !== $executiveOrder->status) {
                EoActivityLog::record($executiveOrder, 'status_changed', ['status' => $oldStatus], ['status' => $executiveOrder->status], $validated['status_notes'] ?? $validated['log_notes'] ?? null);

                // Notify the original uploader if someone else changed the status
                $uploader = $executiveOrder->uploader;
                if ($uploader && $uploader->id !== auth()->id()) {
                    $uploader->notify(new EoStatusChanged($executiveOrder, $oldStatus, $executiveOrder->status, auth()->user()));
                }

                // Fix 3: Notify all admins on any status change, regardless of who made it.
                // Exclude the editor themselves to avoid self-notification.
                $admins = User::where('role', 'admin')->where('id', '!=', auth()->id())->get();
                foreach ($admins as $admin) {
                    // Skip if admin is the uploader — they already got the uploader notification above
                    if ($uploader && $admin->id === $uploader->id) {
                        continue;
                    }
                    $admin->notify(new EoStatusChanged($executiveOrder, $oldStatus, $executiveOrder->status, auth()->user()));
                }
            } else {
                EoActivityLog::record($executiveOrder, 'updated', $oldValues, $newValues, $validated['log_notes'] ?? null);

                // Notify the original uploader if someone else updated their EO
                $uploader = $executiveOrder->uploader;
                if ($uploader && $uploader->id !== auth()->id()) {
                    $uploader->notify(new EoUpdated($executiveOrder, auth()->user()));
                }

                // Fix 4: Notify all admins on content-only edits, regardless of who edited.
                // Exclude the editor and the uploader (already notified above).
                $admins = User::where('role', 'admin')->where('id', '!=', auth()->id())->get();
                foreach ($admins as $admin) {
                    if ($uploader && $admin->id === $uploader->id) {
                        continue;
                    }
                    $admin->notify(new EoUpdated($executiveOrder, auth()->user()));
                }
            }
        });

        return redirect()
            ->route('executive-orders.show', $executiveOrder)
            ->with('success', 'Executive Order updated successfully.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(ExecutiveOrder $executiveOrder)
    {
        // Capture details before deletion for notifications
        $eoNumber  = $executiveOrder->eo_number;
        $eoTitle   = $executiveOrder->title;
        $uploader  = $executiveOrder->uploader;
        $deletedBy = auth()->user();

        EoActivityLog::record($executiveOrder, 'deleted', ['eo_number' => $eoNumber], null);
        $executiveOrder->delete();

        // Fix 5: Notify the original uploader their EO was deleted (if it wasn't them)
        if ($uploader && $uploader->id !== $deletedBy->id) {
            $uploader->notify(new EoDeleted($eoNumber, $eoTitle, $deletedBy));
        }

        // Notify all other admins about the deletion
        $admins = User::where('role', 'admin')->where('id', '!=', $deletedBy->id)->get();
        foreach ($admins as $admin) {
            // Skip uploader — already notified above
            if ($uploader && $admin->id === $uploader->id) {
                continue;
            }
            $admin->notify(new EoDeleted($eoNumber, $eoTitle, $deletedBy));
        }

        return redirect()
            ->route('executive-orders.index')
            ->with('success', "Executive Order {$eoNumber} has been deleted.");
    }

    // ─── View PDF (inline) ────────────────────────────────────────────────────

    public function viewPdf(ExecutiveOrder $executiveOrder)
    {
        if (! Storage::disk('local')->exists($executiveOrder->pdf_path)) {
            abort(404, 'PDF file not found.');
        }

        EoActivityLog::record($executiveOrder, 'pdf_viewed');

        $fullPath = Storage::disk('local')->path($executiveOrder->pdf_path);

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $executiveOrder->original_filename . '"',
        ]);
    }

    // ─── Download PDF ─────────────────────────────────────────────────────────

    public function download(ExecutiveOrder $executiveOrder)
    {
        if (! Storage::disk('local')->exists($executiveOrder->pdf_path)) {
            abort(404, 'PDF file not found.');
        }

        EoActivityLog::record($executiveOrder, 'downloaded');

        $fullPath = Storage::disk('local')->path($executiveOrder->pdf_path);

        return response()->download($fullPath, $executiveOrder->original_filename);
    }
}
