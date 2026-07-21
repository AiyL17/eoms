<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocActivityLog;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = DocActivityLog::with(['user', 'document'])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('doc_type')) {
            $query->whereHas('document', function ($q) use ($request) {
                $q->withTrashed()->where('document_type', $request->doc_type);
            });
        }

        if ($request->filled('search')) {
            $query->whereHas('document', function ($q) use ($request) {
                $q->withTrashed()
                  ->where('doc_number', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%');
            });
        }

        // ── Sorting ───────────────────────────────────────────────────────────
        $sortable = ['created_at', 'action'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';

        if ($sort) {
            $query->reorder()->orderBy($sort, $dir);
        }

        $logs     = $query->paginate(25)->withQueryString();
        $users    = User::orderBy('name')->get(['id', 'name']);
        $docTypes = Document::documentTypes();

        return view('admin.logs.index', compact('logs', 'users', 'docTypes', 'sort', 'dir'));
    }
}
