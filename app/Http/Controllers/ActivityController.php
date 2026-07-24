<?php

namespace App\Http\Controllers;

use App\Models\DocActivityLog;
use App\Models\Document;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = DocActivityLog::with('document')
            ->where('user_id', auth()->id())
            ->latest();

        if ($request->filled('search')) {
            $query->whereHas('document', function ($q) use ($request) {
                $q->withTrashed()
                  ->where('reference_number', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('doc_type')) {
            $query->whereHas('document', function ($q) use ($request) {
                $q->withTrashed()->where('document_type', $request->doc_type);
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // ── Sorting ───────────────────────────────────────────────────────────
        $sortable = ['created_at', 'action'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';

        if ($sort) {
            $query->reorder()->orderBy($sort, $dir);
        }

        $logs     = $query->paginate(25)->withQueryString();
        $docTypes = Document::documentTypes();
        $actions  = [
            'created'       => 'Uploaded',
            'updated'       => 'Updated',
            'deleted'       => 'Archived',
            'restored'      => 'Restored',
            'downloaded'    => 'Downloaded PDF',
            'pdf_viewed'    => 'Viewed PDF',
            'force_deleted' => 'Permanently Deleted',
        ];

        return view('activity.index', compact('logs', 'docTypes', 'actions', 'sort', 'dir'));
    }
}
