<?php

namespace App\Http\Controllers;

use App\Models\EoActivityLog;
use App\Models\ExecutiveOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Core EO counts ────────────────────────────────────────────────────
        $totalEos    = ExecutiveOrder::count();
        $activeEos   = ExecutiveOrder::where('status', 'active')->count();
        $amendedEos  = ExecutiveOrder::where('status', 'amended')->count();
        $thisYearEos = ExecutiveOrder::where('year', date('Y'))->count();

        // ── Status distribution (all 6 statuses) ─────────────────────────────
        $statusCounts = ExecutiveOrder::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── EOs per year ──────────────────────────────────────────────────────
        $yearList = ExecutiveOrder::selectRaw('year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        // ── Recent uploads ────────────────────────────────────────────────────
        $recentEos = ExecutiveOrder::with('uploader')
            ->latest()
            ->take(5)
            ->get();

        // ── Recent activity ───────────────────────────────────────────────────
        $recentLogs = EoActivityLog::with(['user', 'executiveOrder'])
            ->latest()
            ->take(10)
            ->get();

        // ── Admin-only metrics ────────────────────────────────────────────────
        $totalUsers         = User::count();
        $adminCount         = User::where('role', 'admin')->count();
        $staffCount         = User::where('role', 'staff')->count();
        $totalLogs          = EoActivityLog::count();
        $totalDownloads     = EoActivityLog::where('action', 'downloaded')->count();
        $totalPdfViews      = EoActivityLog::where('action', 'pdf_viewed')->count();
        $repealedEos        = ExecutiveOrder::where('status', 'repealed')->count();
        $needsReviewCount   = ExecutiveOrder::whereIn('status', ['under_review', 'suspended'])->count();
        $thisMonthDownloads = EoActivityLog::where('action', 'downloaded')
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        // Total storage used by PDFs (sum of file_size in bytes)
        $totalStorageBytes = ExecutiveOrder::sum('file_size');

        // ── Most active users (by log entries in last 30 days) ────────────────
        $topUsers = EoActivityLog::select('user_id', DB::raw('COUNT(*) as action_count'))
            ->with('user')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('action_count')
            ->take(5)
            ->get();

        // ── EOs added in last 7 days (for the mini spark) ─────────────────────
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->format('D'),
                'count' => ExecutiveOrder::whereDate('created_at', $date->toDateString())->count(),
            ];
        });

        // ── Statuses that need attention (non-active, non-amended) ────────────
        $needsAttention = ExecutiveOrder::whereIn('status', ['under_review', 'suspended'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalEos',
            'activeEos',
            'amendedEos',
            'thisYearEos',
            'statusCounts',
            'yearList',
            'recentEos',
            'recentLogs',
            'totalUsers',
            'adminCount',
            'staffCount',
            'totalLogs',
            'totalDownloads',
            'totalPdfViews',
            'totalStorageBytes',
            'topUsers',
            'last7Days',
            'needsAttention',
            'repealedEos',
            'needsReviewCount',
            'thisMonthDownloads'
        ));
    }
}
