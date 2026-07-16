<?php

namespace App\Http\Controllers;

use App\Models\EoActivityLog;
use App\Models\ExecutiveOrder;
use App\Models\Setting;
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

        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
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

        // Collect all distinct tags for the tag filter dropdown
        $allTags = ExecutiveOrder::whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('executive-orders.index', compact('orders', 'years', 'statuses', 'sort', 'dir', 'allTags'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create()
    {
        // Enforce the staff_can_upload setting for non-admin users
        if (! auth()->user()->isAdmin() && Setting::get('staff_can_upload', '1') !== '1') {
            abort(403, 'Uploading new Executive Orders has been disabled for staff by an administrator.');
        }

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
        // Enforce the staff_can_upload setting for non-admin users
        if (! auth()->user()->isAdmin() && Setting::get('staff_can_upload', '1') !== '1') {
            abort(403, 'Uploading new Executive Orders has been disabled for staff by an administrator.');
        }
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

            // Handle e-signature: save base64 PNG to disk, store path
            $sigPath  = null;
            $sigValue = $validated['signature_data'] ?? null;
            if ($sigValue) {
                $sigPath = $this->saveSignatureFile($sigValue, 'eo-temp-' . uniqid());
            }

            $eo = ExecutiveOrder::create([
                'eo_number'        => $eoNumber,
                'item_number'      => $validated['item_number'],
                'year'             => $validated['year'],
                'title'            => $validated['title'],
                'subject'          => $validated['subject'],
                'content_summary'  => $validated['content_summary'] ?? null,
                'date_issued'      => $validated['date_issued'],
                'date_effective'   => $validated['date_effective'] ?? null,
                'signed_by'        => $validated['signed_by'],
                'pdf_path'         => $storedPath,
                'original_filename'=> $originalFilename,
                'file_size'        => $file->getSize(),
                'status'           => $validated['status'],
                'status_notes'     => $validated['status_notes'] ?? null,
                'amends_id'        => $validated['amends_id'] ?? null,
                'tags'             => $tags,
                'signature_path'   => $sigPath,
                'uploaded_by'      => auth()->id(),
            ]);

            // If the temp path used a placeholder ID, rename to the real EO ID
            if ($sigPath) {
                $realPath = "signatures/executive-orders/{$eo->id}.png";
                Storage::disk('local')->move($sigPath, $realPath);
                $eo->updateQuietly(['signature_path' => $realPath]);
            }

            // If user has no profile signature yet and drew one here, save it to their profile
            if ($sigValue && ! auth()->user()->signature_path) {
                $profileSigPath = "signatures/users/" . auth()->id() . ".png";
                $this->saveSignatureFile($sigValue, null, $profileSigPath);
                auth()->user()->update(['signature_path' => $profileSigPath]);
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

        // Build the full amendment chain for the visualizer
        $chainTree = $this->buildAmendmentChain($executiveOrder);

        return view('executive-orders.show', [
            'eo'        => $executiveOrder,
            'chainTree' => $chainTree,
        ]);
    }

    // ─── Amendment Chain API ──────────────────────────────────────────────────

    public function amendmentChain(ExecutiveOrder $executiveOrder)
    {
        $chain = $this->buildAmendmentChain($executiveOrder);
        return response()->json($chain);
    }

    /**
     * Walks the amendment chain in both directions to build a flat ordered list.
     * Starts from the oldest ancestor, walks forward through all amendments.
     * Only returns a non-empty array when this EO is part of an amendment relationship.
     */
    private function buildAmendmentChain(ExecutiveOrder $eo): array
    {
        // If this EO has no amendment relationships at all, return empty — no chain to show.
        if (! $eo->amends_id && ! $eo->amended_by_id) {
            return [];
        }

        // Walk backward to find the root (oldest) EO
        $root    = $eo;
        $visited = [];
        while ($root->amends_id && ! in_array($root->amends_id, $visited)) {
            $visited[] = $root->id;
            $parent    = ExecutiveOrder::withTrashed()->find($root->amends_id);
            if (! $parent) break;
            $root = $parent;
        }

        // Walk forward from root, collecting nodes in order
        $chain   = [];
        $current = $root;
        $seen    = [];
        while ($current && ! in_array($current->id, $seen)) {
            $seen[] = $current->id;
            $chain[] = [
                'id'          => $current->id,
                'eo_number'   => $current->eo_number,
                'title'       => $current->title,
                'status'      => $current->status,
                'status_label'=> $current->status_label,
                'date_issued' => $current->date_issued?->format('M d, Y'),
                'signed_by'   => $current->signed_by,
                'is_current'  => $current->id === $eo->id,
                'is_trashed'  => (bool) $current->deleted_at,
                'url'         => $current->deleted_at
                    ? route('executive-orders.archive')
                    : route('executive-orders.show', $current->id),
            ];
            if ($current->amended_by_id) {
                $current = ExecutiveOrder::withTrashed()->find($current->amended_by_id);
            } else {
                break;
            }
        }

        return $chain;
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
            'signature_data'  => ['nullable', 'string', 'max:200000', 'regex:/^(CLEAR|data:image\/png;base64,[A-Za-z0-9+\/]+=*)$/'],
            'log_notes'       => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $validated, $executiveOrder) {
            $oldValues = $executiveOrder->only(['title', 'subject', 'status', 'date_issued', 'date_effective', 'signed_by']);
            $oldStatus = $executiveOrder->status;

            // Handle PDF replacement — archive the old file instead of deleting it
            if ($request->hasFile('pdf_file')) {
                // Move old PDF into a versioned archive folder
                $oldPath = $executiveOrder->pdf_path;
                if ($oldPath && Storage::disk('local')->exists($oldPath)) {
                    $archivePath = 'executive-orders-archive/'
                        . $executiveOrder->id . '/'
                        . now()->format('Y-m-d_His') . '_' . basename($oldPath);
                    Storage::disk('local')->move($oldPath, $archivePath);
                }

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

            // Handle signature: keep existing if blank, clear if 'CLEAR', save new to disk
            $sigValue = $validated['signature_data'] ?? null;
            unset($validated['signature_data']); // remove raw base64 — we use signature_path instead

            if ($sigValue === 'CLEAR') {
                // Delete old signature file and null the path
                if ($executiveOrder->signature_path) {
                    Storage::disk('local')->delete($executiveOrder->signature_path);
                }
                $validated['signature_path'] = null;
            } elseif ($sigValue) {
                // Delete old file and write new one
                if ($executiveOrder->signature_path) {
                    Storage::disk('local')->delete($executiveOrder->signature_path);
                }
                $sigPath = "signatures/executive-orders/{$executiveOrder->id}.png";
                $this->saveSignatureFile($sigValue, null, $sigPath);
                $validated['signature_path'] = $sigPath;

                // If user has no profile signature yet, save it there too
                if (! auth()->user()->signature_path) {
                    $profileSigPath = "signatures/users/" . auth()->id() . ".png";
                    $this->saveSignatureFile($sigValue, null, $profileSigPath);
                    auth()->user()->update(['signature_path' => $profileSigPath]);
                }
            }
            // else: empty / null — leave signature_path unchanged (don't include in update array)

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

    // ─── Destroy (soft delete) ────────────────────────────────────────────────

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
            ->with('success', "Executive Order {$eoNumber} has been archived.");
    }

    // ─── Archive: list soft-deleted EOs ──────────────────────────────────────

    public function archive(Request $request)
    {
        $query = ExecutiveOrder::onlyTrashed()->with('uploader');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortable = ['eo_number', 'title', 'deleted_at', 'uploaded_by'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';

        if ($sort === 'uploaded_by') {
            // Join users table to sort by uploader name
            $query->leftJoin('users', 'users.id', '=', 'executive_orders.uploaded_by')
                  ->orderBy('users.name', $dir)
                  ->select('executive_orders.*');
        } elseif ($sort) {
            $query->orderBy($sort, $dir);
        } else {
            $query->latest('deleted_at');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('executive-orders.archive', compact('orders', 'sort', 'dir'));
    }

    // ─── Restore a soft-deleted EO ───────────────────────────────────────────

    public function restore(int $id)
    {
        $executiveOrder = ExecutiveOrder::onlyTrashed()->findOrFail($id);
        $executiveOrder->restore();

        EoActivityLog::record($executiveOrder, 'restored', null, [
            'eo_number' => $executiveOrder->eo_number,
        ]);

        return redirect()
            ->route('executive-orders.archive')
            ->with('success', "Executive Order {$executiveOrder->eo_number} has been restored.");
    }

    // ─── Force-delete (permanent) ─────────────────────────────────────────────

    public function forceDestroy(int $id)
    {
        $executiveOrder = ExecutiveOrder::onlyTrashed()->findOrFail($id);

        // Permanently remove the live PDF if it still exists
        if ($executiveOrder->pdf_path && Storage::disk('local')->exists($executiveOrder->pdf_path)) {
            Storage::disk('local')->delete($executiveOrder->pdf_path);
        }

        // Remove any archived (versioned) PDFs
        $archiveDir = 'executive-orders-archive/' . $executiveOrder->id;
        if (Storage::disk('local')->directoryExists($archiveDir)) {
            Storage::disk('local')->deleteDirectory($archiveDir);
        }

        // Remove signature file
        if ($executiveOrder->signature_path && Storage::disk('local')->exists($executiveOrder->signature_path)) {
            Storage::disk('local')->delete($executiveOrder->signature_path);
        }

        $eoNumber = $executiveOrder->eo_number;
        EoActivityLog::record($executiveOrder, 'force_deleted', ['eo_number' => $eoNumber], null);
        $executiveOrder->forceDelete();

        return redirect()
            ->route('executive-orders.archive')
            ->with('success', "Executive Order {$eoNumber} has been permanently deleted.");
    }

    // ─── Version History ──────────────────────────────────────────────────────

    public function versionHistory(ExecutiveOrder $executiveOrder)
    {
        $executiveOrder->load('activityLogs.user');

        // Gather archived (versioned) PDF files from the archive directory
        $archiveDir   = 'executive-orders-archive/' . $executiveOrder->id;
        $archivedFiles = [];

        if (Storage::disk('local')->directoryExists($archiveDir)) {
            $files = Storage::disk('local')->files($archiveDir);
            foreach (array_reverse($files) as $file) {
                $basename  = basename($file);
                // Filename format: 2026-07-15_143020_original.pdf
                $timestamp = null;
                if (preg_match('/^(\d{4}-\d{2}-\d{2}_\d{6})_/', $basename, $m)) {
                    $timestamp = \Carbon\Carbon::createFromFormat('Y-m-d_His', $m[1]);
                }
                $archivedFiles[] = [
                    'path'      => $file,
                    'filename'  => $basename,
                    'size'      => Storage::disk('local')->size($file),
                    'timestamp' => $timestamp,
                ];
            }
        }

        // Metadata change diffs from activity logs
        $metaDiffs = $executiveOrder->activityLogs
            ->whereIn('action', ['updated', 'status_changed'])
            ->map(function ($log) {
                return [
                    'id'        => $log->id,
                    'action'    => $log->action,
                    'label'     => $log->action_label,
                    'color'     => $log->action_color,
                    'user'      => $log->user?->name ?? 'System',
                    'notes'     => $log->notes,
                    'old'       => $log->old_values ?? [],
                    'new'       => $log->new_values ?? [],
                    'created_at'=> $log->created_at,
                ];
            })
            ->values();

        return view('executive-orders.version-history', [
            'eo'            => $executiveOrder,
            'archivedFiles' => $archivedFiles,
            'metaDiffs'     => $metaDiffs,
        ]);
    }

    // ─── Download archived PDF version ───────────────────────────────────────

    public function downloadArchived(ExecutiveOrder $executiveOrder, Request $request)
    {
        $file = $request->query('file');
        // Security: ensure the path stays within the EO's own archive dir
        $allowed = 'executive-orders-archive/' . $executiveOrder->id . '/';
        if (! $file || ! str_starts_with($file, $allowed) || ! Storage::disk('local')->exists($file)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('local')->path($file),
            basename($file)
        );
    }

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

    // ─── Serve EO signature image from local disk ─────────────────────────────

    public function serveSignature(ExecutiveOrder $executiveOrder)
    {
        if (! $executiveOrder->signature_path || ! Storage::disk('local')->exists($executiveOrder->signature_path)) {
            abort(404);
        }

        return response(Storage::disk('local')->get($executiveOrder->signature_path), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Decode a base64 PNG data URI and store it on the local disk.
     * Pass $path to write to a specific path, or $prefix to auto-name it.
     */
    private function saveSignatureFile(string $blob, ?string $prefix = null, ?string $path = null): ?string
    {
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $blob);
        $data   = base64_decode($base64);

        if (! $data) {
            return null;
        }

        $target = $path ?? "signatures/{$prefix}.png";
        Storage::disk('local')->put($target, $data);

        return $target;
    }
}
