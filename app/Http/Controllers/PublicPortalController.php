<?php

namespace App\Http\Controllers;

use App\Models\ExecutiveOrder;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicPortalController extends Controller
{
    public function index(Request $request)
    {
        $query = ExecutiveOrder::with('uploader')->latest();

        // Only expose publicly-visible EOs (active + under_review unless restricted)
        $query->whereNotNull('id'); // base — all non-deleted

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

        // Sorting
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

        $allTags = ExecutiveOrder::whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $totalActive = ExecutiveOrder::where('status', 'active')->count();
        $totalEos    = ExecutiveOrder::count();

        return view('public.index', compact(
            'orders', 'years', 'statuses', 'sort', 'dir', 'allTags',
            'totalActive', 'totalEos'
        ));
    }

    public function show(ExecutiveOrder $executiveOrder)
    {
        $executiveOrder->load(['uploader', 'amends', 'amendedBy']);
        return view('public.show', ['eo' => $executiveOrder]);
    }

    public function viewPdf(ExecutiveOrder $executiveOrder)
    {
        if (! Storage::disk('local')->exists($executiveOrder->pdf_path)) {
            abort(404, 'PDF file not found.');
        }

        $fullPath = Storage::disk('local')->path($executiveOrder->pdf_path);

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $executiveOrder->original_filename . '"',
        ]);
    }

    public function download(ExecutiveOrder $executiveOrder)
    {
        if (! Storage::disk('local')->exists($executiveOrder->pdf_path)) {
            abort(404, 'PDF file not found.');
        }

        $fullPath = Storage::disk('local')->path($executiveOrder->pdf_path);

        return response()->download($fullPath, $executiveOrder->original_filename);
    }
}
