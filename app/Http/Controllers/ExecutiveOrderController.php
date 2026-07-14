<?php

namespace App\Http\Controllers;

use App\Models\EoActivityLog;
use App\Models\ExecutiveOrder;
use App\Models\User;
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
            'content_summary' => 'nullable|string',
            'date_issued'     => 'required|date',
            'date_effective'  => 'nullable|date',
            'signed_by'       => 'required|string|max:255',
            'pdf_file'        => 'required|file|mimes:pdf|max:20480',
            'status'          => 'required|in:active,amended,repealed,suspended,superseded,under_review',
            'status_notes'    => 'nullable|string|max:1000',
            'amends_id'       => 'nullable|exists:executive_orders,id',
            'tags'            => 'nullable|string',
        ], [
            'item_number.unique' => 'An executive order with this item number already exists for the selected year.',
            'pdf_file.max'       => 'The PDF file must not exceed 20 MB.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // Store the PDF
            $file             = $request->file('pdf_file');
            $originalFilename = $file->getClientOriginalName();
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
                'uploaded_by'     => auth()->id(),
            ]);

            // If this EO amends another, update the original EO's status
            if (! empty($validated['amends_id'])) {
                $original = ExecutiveOrder::find($validated['amends_id']);
                if ($original) {
                    $original->update([
                        'status'        => 'amended',
                        'status_notes'  => "Amended by {$eoNumber}",
                        'amended_by_id' => $eo->id,
                        'updated_by'    => auth()->id(),
                    ]);
                    EoActivityLog::record($original, 'status_changed', ['status' => $original->getOriginal('status')], ['status' => 'amended'], "Amended by {$eoNumber}");
                }
            }

            // Log the creation
            EoActivityLog::record($eo, 'created', null, [
                'eo_number' => $eoNumber,
                'title'     => $eo->title,
                'status'    => $eo->status,
            ]);

            // Notify all admins that a new EO was uploaded
            $uploader = auth()->user();
            if ($uploader->isStaff()) {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new EoUploaded($eo, $uploader));
                }
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
            'content_summary' => 'nullable|string',
            'date_issued'     => 'required|date',
            'date_effective'  => 'nullable|date',
            'signed_by'       => 'required|string|max:255',
            'status'          => 'required|in:active,amended,repealed,suspended,superseded,under_review',
            'status_notes'    => 'nullable|string|max:1000',
            'tags'            => 'nullable|string',
            'pdf_file'        => 'nullable|file|mimes:pdf|max:20480',
        ]);

        DB::transaction(function () use ($request, $validated, $executiveOrder) {
            $oldValues = $executiveOrder->only(['title', 'subject', 'status', 'date_issued', 'date_effective', 'signed_by']);
            $oldStatus = $executiveOrder->status;

            // Handle PDF replacement
            if ($request->hasFile('pdf_file')) {
                // Delete old file
                Storage::disk('local')->delete($executiveOrder->pdf_path);
                $file = $request->file('pdf_file');
                $validated['pdf_path']         = $file->store('executive-orders', 'local');
                $validated['original_filename'] = $file->getClientOriginalName();
                $validated['file_size']         = $file->getSize();
            }

            // Parse tags
            if (isset($validated['tags'])) {
                $tags = array_filter(array_map('trim', explode(',', $validated['tags'])));
                $validated['tags'] = array_values($tags) ?: null;
            }

            $validated['updated_by'] = auth()->id();
            $executiveOrder->update($validated);

            $newValues = $executiveOrder->only(['title', 'subject', 'status', 'date_issued', 'date_effective', 'signed_by']);

            // Log status change separately if status changed
            if ($oldStatus !== $executiveOrder->status) {
                EoActivityLog::record($executiveOrder, 'status_changed', ['status' => $oldStatus], ['status' => $executiveOrder->status], $validated['status_notes'] ?? null);

                // Notify the original uploader if someone else changed the status
                $uploader = $executiveOrder->uploader;
                if ($uploader && $uploader->id !== auth()->id()) {
                    $uploader->notify(new EoStatusChanged($executiveOrder, $oldStatus, $executiveOrder->status, auth()->user()));
                }

                // Notify all admins if a staff member changed the status
                if (auth()->user()->isStaff()) {
                    $admins = User::where('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new EoStatusChanged($executiveOrder, $oldStatus, $executiveOrder->status, auth()->user()));
                    }
                }
            } else {
                EoActivityLog::record($executiveOrder, 'updated', $oldValues, $newValues);

                // Notify the original uploader if someone else updated their EO
                $uploader = $executiveOrder->uploader;
                if ($uploader && $uploader->id !== auth()->id()) {
                    $uploader->notify(new EoUpdated($executiveOrder, auth()->user()));
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
        EoActivityLog::record($executiveOrder, 'deleted', ['eo_number' => $executiveOrder->eo_number], null);
        $executiveOrder->delete();

        return redirect()
            ->route('executive-orders.index')
            ->with('success', "Executive Order {$executiveOrder->eo_number} has been deleted.");
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
