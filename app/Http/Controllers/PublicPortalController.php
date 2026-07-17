<?php

namespace App\Http\Controllers;

use App\Models\ExecutiveOrder;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PublicPortalController extends Controller
{
    /**
     * Statuses visible to the general public.
     * Under-review / draft EOs are internal until published.
     */
    const PUBLIC_STATUSES = ['active', 'amended', 'repealed', 'suspended', 'superseded'];

    public function index(Request $request)
    {
        $query = ExecutiveOrder::with('uploader')
            ->whereIn('status', self::PUBLIC_STATUSES)
            ->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            // Only allow filtering by publicly-visible statuses
            if (in_array($request->status, self::PUBLIC_STATUSES)) {
                $query->byStatus($request->status);
            }
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

        // Cache metadata that changes infrequently — refresh every 5 minutes
        [$years, $statuses, $allTags, $totalActive, $totalEos, $thisYearCount] =
            Cache::remember('public_portal_meta', 300, function () {
                $years = ExecutiveOrder::whereIn('status', self::PUBLIC_STATUSES)
                    ->selectRaw('year')
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');

                // Only expose public statuses in the filter dropdown
                $statuses = array_intersect_key(
                    ExecutiveOrder::statuses(),
                    array_flip(self::PUBLIC_STATUSES)
                );

                $allTags = ExecutiveOrder::whereIn('status', self::PUBLIC_STATUSES)
                    ->whereNotNull('tags')
                    ->selectRaw('tags')
                    ->pluck('tags')
                    ->flatten()
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();

                $totalActive   = ExecutiveOrder::where('status', 'active')->count();
                $totalEos      = ExecutiveOrder::whereIn('status', self::PUBLIC_STATUSES)->count();
                $thisYearCount = ExecutiveOrder::whereIn('status', self::PUBLIC_STATUSES)
                    ->where('year', date('Y'))
                    ->count();

                return [$years, $statuses, $allTags, $totalActive, $totalEos, $thisYearCount];
            });

        return view('public.index', compact(
            'orders', 'years', 'statuses', 'sort', 'dir', 'allTags',
            'totalActive', 'totalEos', 'thisYearCount'
        ));
    }

    public function show(ExecutiveOrder $executiveOrder)
    {
        // Block access to internal/draft EOs
        if (! in_array($executiveOrder->status, self::PUBLIC_STATUSES)) {
            abort(404);
        }

        $executiveOrder->load(['uploader', 'amends', 'amendedBy']);
        return view('public.show', ['eo' => $executiveOrder]);
    }

    public function viewPdf(ExecutiveOrder $executiveOrder)
    {
        if (! in_array($executiveOrder->status, self::PUBLIC_STATUSES)) {
            abort(404);
        }

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
        if (! in_array($executiveOrder->status, self::PUBLIC_STATUSES)) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($executiveOrder->pdf_path)) {
            abort(404, 'PDF file not found.');
        }

        $fullPath = Storage::disk('local')->path($executiveOrder->pdf_path);

        return response()->download($fullPath, $executiveOrder->original_filename);
    }
}
