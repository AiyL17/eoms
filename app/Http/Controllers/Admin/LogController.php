<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EoActivityLog;
use App\Models\ExecutiveOrder;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = EoActivityLog::with(['user', 'executiveOrder'])->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('eo_id')) {
            $query->where('executive_order_id', $request->eo_id);
        }

        if ($request->filled('search')) {
            $query->whereHas('executiveOrder', function ($q) use ($request) {
                $q->withTrashed()
                  ->where('eo_number', 'like', '%' . $request->search . '%')
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

        $logs    = $query->paginate(25)->withQueryString();
        $users   = User::orderBy('name')->get(['id', 'name']);
        $orders  = ExecutiveOrder::withTrashed()->orderBy('year', 'desc')->orderBy('item_number')->get(['id', 'eo_number']);
        $actions = [
            'created'        => 'Uploaded',
            'updated'        => 'Updated',
            'status_changed' => 'Status Changed',
            'deleted'        => 'Deleted',
            'downloaded'     => 'Downloaded PDF',
            'pdf_viewed'     => 'Viewed PDF',
        ];

        return view('admin.logs.index', compact('logs', 'users', 'orders', 'actions', 'sort', 'dir'));
    }
}
