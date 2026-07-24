<?php

namespace App\Http\Controllers;

use App\Models\DocActivityLog;
use App\Models\Document;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\DocDeleted;
use App\Notifications\DocUploaded;
use App\Notifications\DocTypeChanged;
use App\Notifications\DocUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Document::with('uploader')->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // ── My Documents filter (staff only) ──────────────────────────────────
        $myDocs = $request->boolean('my_docs');
        if ($myDocs) {
            $query->where('uploaded_by', auth()->id());
        }

        // ── Uploader filter (admin only) ───────────────────────────────────────
        $uploaderFilter = null;
        $uploadersList  = collect();
        if (auth()->user()->isAdmin()) {
            $uploadersList = User::orderBy('name')->get(['id', 'name']);
            if ($request->filled('uploader')) {
                $uploaderFilter = (int) $request->uploader;
                $query->where('uploaded_by', $uploaderFilter);
            }
        }

        // ── Sorting ───────────────────────────────────────────────────────────
        $sortable = ['reference_number', 'date_issued', 'expiration_date'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';

        if ($sort) {
            $query->reorder()->orderBy($sort, $dir);
        }

        $orders = $query->paginate(15)->withQueryString();

        $documentTypes = Document::documentTypes();

        return view('documents.index', compact(
            'orders', 'sort', 'dir', 'documentTypes', 'myDocs',
            'uploaderFilter', 'uploadersList'
        ));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create()
    {
        // Enforce the staff_can_upload setting for non-admin users
        if (! auth()->user()->isAdmin() && Setting::get('staff_can_upload', '1') !== '1') {
            abort(403, 'Uploading new documents has been disabled for staff by an administrator.');
        }

        return view('documents.create', [
            'documentTypes' => Document::documentTypes(),
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        // Enforce the staff_can_upload setting for non-admin users
        if (! auth()->user()->isAdmin() && Setting::get('staff_can_upload', '1') !== '1') {
            abort(403, 'Uploading new documents has been disabled for staff by an administrator.');
        }

        $validated = $request->validate([
            'reference_number'=> 'required|string|max:255|unique:documents,reference_number',
            'title'           => 'required|string|max:500',
            'document_type'   => 'required|in:incoming,outgoing',
            'received_from'   => 'required|string|max:255',
            'date_issued'     => 'required|date',
            'expiration_date' => 'nullable|date|after:date_issued',
            'recipient'       => 'required|string|max:255',
            'pdf_file'        => 'required|file|mimes:pdf|max:20480',
        ], [
            'reference_number.required' => 'The reference number is required.',
            'reference_number.unique'   => 'This reference number is already in use.',
            'pdf_file.max'              => 'The PDF file must not exceed 20 MB.',
            'expiration_date.after'     => 'The expiration date must be after the date received.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // Store the PDF
            $file             = $request->file('pdf_file');
            $originalFilename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $file->getClientOriginalName());
            $storedPath       = $file->store('documents', 'local');

            $doc = Document::create([
                'reference_number'  => $validated['reference_number'],
                'document_type'     => $validated['document_type'],
                'title'             => $validated['title'],
                'received_from'     => $validated['received_from'],
                'recipient'         => $validated['recipient'],
                'date_issued'       => $validated['date_issued'],
                'expiration_date'   => $validated['expiration_date'] ?? null,
                'pdf_path'          => $storedPath,
                'original_filename' => $originalFilename,
                'file_size'         => $file->getSize(),
                'uploaded_by'       => auth()->id(),
            ]);

            // Log the creation
            DocActivityLog::record($doc, 'created', null, [
                'reference_number' => $doc->reference_number,
                'title'            => $doc->title,
            ]);

            // Notify all admins that a new document was registered
            $uploader = auth()->user();
            $admins   = User::where('role', 'admin')->where('id', '!=', $uploader->id)->get();
            foreach ($admins as $admin) {
                $admin->notify(new DocUploaded($doc, $uploader));
            }
        });

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Document $Document)
    {
        $Document->load(['uploader', 'updater', 'activityLogs.user']);

        return view('documents.show', [
            'doc' => $Document,
        ]);
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(Document $Document)
    {
        return view('documents.edit', [
            'doc'           => $Document,
            'documentTypes' => Document::documentTypes(),
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Document $Document)
    {
        $validated = $request->validate([
            'reference_number'=> 'required|string|max:255|unique:documents,reference_number,' . $Document->id,
            'title'           => 'required|string|max:500',
            'document_type'   => 'required|in:incoming,outgoing',
            'received_from'   => 'required|string|max:255',
            'date_issued'     => 'required|date',
            'expiration_date' => 'nullable|date|after:date_issued',
            'recipient'       => 'required|string|max:255',
            'pdf_file'        => 'nullable|file|mimes:pdf|max:20480',
            'log_notes'       => 'nullable|string|max:1000',
        ], [
            'reference_number.required' => 'The reference number is required.',
            'reference_number.unique'   => 'This reference number is already in use.',
            'expiration_date.after'     => 'The expiration date must be after the date received.',
        ]);

        DB::transaction(function () use ($request, $validated, $Document) {
            $oldValues = $Document->only([
                'reference_number', 'title', 'received_from', 'recipient', 'date_issued', 'expiration_date', 'document_type', 'original_filename',
            ]);

            // Capture document_type change before the model is updated
            $oldType = $Document->document_type;
            $newType = $validated['document_type'];

            // Handle PDF replacement — archive the old file instead of deleting it
            if ($request->hasFile('pdf_file')) {
                $oldPath = $Document->pdf_path;
                if ($oldPath && Storage::disk('local')->exists($oldPath)) {
                    // Use the document's original_filename so the archive has a meaningful name
                    $safeName    = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $Document->original_filename);
                    $archivePath = 'documents-archive/'
                        . $Document->id . '/'
                        . now()->format('Y-m-d_His') . '_' . $safeName;
                    Storage::disk('local')->move($oldPath, $archivePath);
                }

                $file = $request->file('pdf_file');
                $validated['pdf_path']          = $file->store('documents', 'local');
                $validated['original_filename'] = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $file->getClientOriginalName());
                $validated['file_size']         = $file->getSize();
            }

            $validated['updated_by'] = auth()->id();
            $logNotes = $validated['log_notes'] ?? null;
            unset($validated['log_notes']); // audit-only field, not a model column

            $Document->update($validated);

            $newValues = $Document->only([
                'reference_number', 'title', 'received_from', 'recipient', 'date_issued', 'expiration_date', 'document_type', 'original_filename',
            ]);

            DocActivityLog::record($Document, 'updated', $oldValues, $newValues, $logNotes);

            $uploader = $Document->uploader;
            if ($uploader && $uploader->id !== auth()->id()) {
                $uploader->notify(new DocUpdated($Document, auth()->user()));
            }

            $admins = User::where('role', 'admin')->where('id', '!=', auth()->id())->get();
            foreach ($admins as $admin) {
                if ($uploader && $admin->id === $uploader->id) continue;
                $admin->notify(new DocUpdated($Document, auth()->user()));
            }

            // If document type changed, notify all admins and staff (excluding the editor)
            if ($oldType !== $newType) {
                $editor     = auth()->user();
                $recipients = User::whereIn('role', ['admin', 'staff'])
                    ->where('id', '!=', $editor->id)
                    ->get();
                foreach ($recipients as $recipient) {
                    $recipient->notify(new DocTypeChanged($Document, $editor, $oldType, $newType));
                }
            }
        });

        return redirect()
            ->route('documents.show', $Document)
            ->with('success', 'Document updated successfully.');
    }

    // ─── Destroy (soft delete) ────────────────────────────────────────────────

    public function destroy(Document $Document)
    {
        $docNumber = $Document->reference_number;
        $docTitle  = $Document->title;
        $uploader  = $Document->uploader;
        $deletedBy = auth()->user();

        DocActivityLog::record($Document, 'deleted', ['reference_number' => $docNumber], null);
        $Document->delete();

        if ($uploader && $uploader->id !== $deletedBy->id) {
            $uploader->notify(new DocDeleted($docNumber, $docTitle, $deletedBy));
        }

        $admins = User::where('role', 'admin')->where('id', '!=', $deletedBy->id)->get();
        foreach ($admins as $admin) {
            if ($uploader && $admin->id === $uploader->id) continue;
            $admin->notify(new DocDeleted($docNumber, $docTitle, $deletedBy));
        }

        return redirect()
            ->route('documents.index')
            ->with('success', "Document {$docNumber} has been archived.");
    }

    // ─── Archive: list soft-deleted documents ─────────────────────────────────

    public function archive(Request $request)
    {
        $query = Document::onlyTrashed()->with('uploader');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $sortable = ['reference_number', 'title', 'deleted_at', 'uploaded_by'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';

        if ($sort === 'uploaded_by') {
            $query->leftJoin('users', 'users.id', '=', 'documents.uploaded_by')
                  ->orderBy('users.name', $dir)
                  ->select('documents.*');
        } elseif ($sort) {
            $query->orderBy($sort, $dir);
        } else {
            $query->latest('deleted_at');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('documents.archive', compact('orders', 'sort', 'dir'));
    }

    // ─── Restore a soft-deleted document ─────────────────────────────────────

    public function restore(int $id)
    {
        $Document = Document::onlyTrashed()->findOrFail($id);
        $Document->restore();

        DocActivityLog::record($Document, 'restored', null, [
            'reference_number' => $Document->reference_number,
        ]);

        return redirect()
            ->route('documents.archive')
            ->with('success', "Document {$Document->reference_number} has been restored.");
    }

    // ─── Force-delete (permanent) ─────────────────────────────────────────────

    public function forceDestroy(int $id)
    {
        $Document = Document::onlyTrashed()->findOrFail($id);

        if ($Document->pdf_path && Storage::disk('local')->exists($Document->pdf_path)) {
            Storage::disk('local')->delete($Document->pdf_path);
        }

        $archiveDir = 'documents-archive/' . $Document->id;
        if (Storage::disk('local')->directoryExists($archiveDir)) {
            Storage::disk('local')->deleteDirectory($archiveDir);
        }

        $docNumber = $Document->reference_number;
        DocActivityLog::record($Document, 'force_deleted', ['reference_number' => $docNumber], null);
        $Document->forceDelete();

        return redirect()
            ->route('documents.archive')
            ->with('success', "Document {$docNumber} has been permanently deleted.");
    }

    // ─── Version History ──────────────────────────────────────────────────────

    public function versionHistory(Document $Document)
    {
        $Document->load('activityLogs.user');

        $archiveDir    = 'documents-archive/' . $Document->id;
        $archivedFiles = [];

        if (Storage::disk('local')->directoryExists($archiveDir)) {
            $files = Storage::disk('local')->files($archiveDir);
            foreach (array_reverse($files) as $file) {
                $basename  = basename($file);
                $timestamp = null;
                if (preg_match('/^(\d{4}-\d{2}-\d{2}_\d{6})_/', $basename, $m)) {
                    $timestamp = \Carbon\Carbon::createFromFormat('Y-m-d_His', $m[1], config('app.timezone'));
                }
                $archivedFiles[] = [
                    'path'          => $file,
                    'filename'      => $basename,
                    'original_name' => preg_replace('/^\d{4}-\d{2}-\d{2}_\d{6}_/', '', $basename),
                    'size'          => Storage::disk('local')->size($file),
                    'timestamp'     => $timestamp,
                ];
            }
        }

        $metaDiffs = $Document->activityLogs
            ->where('action', 'updated')
            ->map(function ($log) {
                return [
                    'id'         => $log->id,
                    'action'     => $log->action,
                    'label'      => $log->action_label,
                    'color'      => $log->action_color,
                    'user'       => $log->user?->name ?? 'System',
                    'notes'      => $log->notes,
                    'old'        => $log->old_values ?? [],
                    'new'        => $log->new_values ?? [],
                    'created_at' => $log->created_at,
                ];
            })
            ->values();

        return view('documents.version-history', [
            'doc'           => $Document,
            'archivedFiles' => $archivedFiles,
            'metaDiffs'     => $metaDiffs,
        ]);
    }

    // ─── Download archived PDF version ───────────────────────────────────────

    public function downloadArchived(Document $Document, Request $request)
    {
        $file    = $request->query('file');
        $allowed = 'documents-archive/' . $Document->id . '/';
        if (! $file || ! str_starts_with($file, $allowed) || ! Storage::disk('local')->exists($file)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('local')->path($file),
            basename($file)
        );
    }

    public function openArchived(Document $Document, Request $request)
    {
        $file    = $request->query('file');
        $allowed = 'documents-archive/' . $Document->id . '/';
        if (! $file || ! str_starts_with($file, $allowed) || ! Storage::disk('local')->exists($file)) {
            abort(404);
        }

        // Strip the timestamp prefix (YYYY-MM-DD_HHMMSS_) to get the original filename
        $basename         = basename($file);
        $originalFilename = preg_replace('/^\d{4}-\d{2}-\d{2}_\d{6}_/', '', $basename);

        return response()->file(Storage::disk('local')->path($file), [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $originalFilename . '"',
        ]);
    }

    // ─── Toggle Document Type ─────────────────────────────────────────────────

    public function toggleType(Document $Document)
    {
        $oldType = $Document->document_type;
        $newType = $oldType === 'incoming' ? 'outgoing' : 'incoming';

        $Document->update([
            'document_type' => $newType,
            'updated_by'    => auth()->id(),
        ]);

        DocActivityLog::record($Document, 'updated',
            ['document_type' => $oldType],
            ['document_type' => $newType]
        );

        // Notify about type change
        $editor     = auth()->user();
        $recipients = User::whereIn('role', ['admin', 'staff'])
            ->where('id', '!=', $editor->id)
            ->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new DocTypeChanged($Document, $editor, $oldType, $newType));
        }

        return back()->with('success', "Document type changed to " . ucfirst($newType) . ".");
    }

    // ─── View PDF inline ─────────────────────────────────────────────────────

    public function viewPdf(Document $Document)
    {
        if (! Storage::disk('local')->exists($Document->pdf_path)) {
            abort(404, 'PDF file not found.');
        }

        DocActivityLog::record($Document, 'pdf_viewed');

        $fullPath = Storage::disk('local')->path($Document->pdf_path);

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $Document->original_filename . '"',
        ]);
    }

    // ─── Download PDF ─────────────────────────────────────────────────────────

    public function download(Document $Document)
    {
        if (! Storage::disk('local')->exists($Document->pdf_path)) {
            abort(404, 'PDF file not found.');
        }

        DocActivityLog::record($Document, 'downloaded');

        $fullPath = Storage::disk('local')->path($Document->pdf_path);

        return response()->download($fullPath, $Document->original_filename);
    }
}
