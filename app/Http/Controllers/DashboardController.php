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
        return auth()->user()->isAdmin()
            ? $this->adminDashboard()
            : $this->staffDashboard();
    }

    // ─── Administrator Dashboard ──────────────────────────────────────────────

    private function adminDashboard()
    {
        // ── Core EO counts ────────────────────────────────────────────────────
        $totalEos    = ExecutiveOrder::count();
        $thisYearEos = ExecutiveOrder::where('year', date('Y'))->count();

        // ── Status distribution ───────────────────────────────────────────────
        $statusCounts = ExecutiveOrder::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── EOs per year ──────────────────────────────────────────────────────
        $yearList = ExecutiveOrder::selectRaw('year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        // ── Recent uploads (system-wide) ─────────────────────────────────────
        $recentEos = ExecutiveOrder::with('uploader')
            ->latest()
            ->take(5)
            ->get();

        // ── Recent activity (system-wide) ─────────────────────────────────────
        $recentLogs = EoActivityLog::with(['user', 'executiveOrder'])
            ->latest()
            ->take(10)
            ->get();

        // ── User stats ────────────────────────────────────────────────────────
        $totalUsers  = User::count();
        $adminCount  = User::where('role', 'admin')->count();
        $staffCount  = User::where('role', 'staff')->count();

        // ── System-wide metrics ───────────────────────────────────────────────
        $totalLogs          = EoActivityLog::count();
        $totalDownloads     = EoActivityLog::where('action', 'downloaded')->count();
        $totalPdfViews      = EoActivityLog::where('action', 'pdf_viewed')->count();
        $needsReviewCount   = ExecutiveOrder::whereIn('status', ['under_review', 'suspended'])->count();
        $thisMonthDownloads = EoActivityLog::where('action', 'downloaded')
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        // ── Most active users (last 30 days) ──────────────────────────────────
        $topUsers = EoActivityLog::select('user_id', DB::raw('COUNT(*) as action_count'))
            ->with('user')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('action_count')
            ->take(5)
            ->get();

        // ── EOs added in last 7 days ──────────────────────────────────────────
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->format('D'),
                'count' => ExecutiveOrder::whereDate('created_at', $date->toDateString())->count(),
            ];
        });

        // ── EOs that need attention ───────────────────────────────────────────
        $needsAttention = ExecutiveOrder::whereIn('status', ['under_review', 'suspended'])
            ->latest()
            ->take(5)
            ->get();

        // ── New users this month ──────────────────────────────────────────────
        $newUsersThisMonth = User::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        // ── EOs due for annual review (active and 1+ year old) ────────────────
        $reviewDueCount = ExecutiveOrder::where('status', 'active')
            ->where('date_issued', '<=', now()->subYear()->toDateString())
            ->count();

        return view('dashboard.admin', compact(
            'totalEos',
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
            'topUsers',
            'last7Days',
            'needsAttention',
            'needsReviewCount',
            'thisMonthDownloads',
            'newUsersThisMonth',
            'reviewDueCount'
        ));
    }

    // ─── Staff Dashboard ──────────────────────────────────────────────────────

    private function staffDashboard()
    {
        $user = auth()->user();

        // ── Personal EO stats ─────────────────────────────────────────────────
        $myTotalUploads = ExecutiveOrder::where('uploaded_by', $user->id)->count();
        $myThisMonth    = ExecutiveOrder::where('uploaded_by', $user->id)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();
        $myRecentEos    = ExecutiveOrder::where('uploaded_by', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // ── Personal activity log ─────────────────────────────────────────────
        $myRecentLogs = EoActivityLog::with('executiveOrder')
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();
        $myTotalActions = EoActivityLog::where('user_id', $user->id)->count();

        // ── Personal download count ───────────────────────────────────────────
        $myDownloads = EoActivityLog::where('user_id', $user->id)
            ->where('action', 'downloaded')
            ->count();

        // ── System-wide EO overview (read-only context) ───────────────────────
        $totalEos     = ExecutiveOrder::count();
        $activeEos    = ExecutiveOrder::where('status', 'active')->count();
        $thisYearEos  = ExecutiveOrder::where('year', date('Y'))->count();
        $statusCounts = ExecutiveOrder::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── EOs that need attention ───────────────────────────────────────────
        $needsAttention = ExecutiveOrder::whereIn('status', ['under_review', 'suspended'])
            ->latest()
            ->take(5)
            ->get();

        // ── Recent EOs across system (for browsing) ───────────────────────────
        $recentEos = ExecutiveOrder::with('uploader')
            ->latest()
            ->take(5)
            ->get();

        // ── My uploads in last 7 days ─────────────────────────────────────────
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) use ($user) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->format('D'),
                'count' => ExecutiveOrder::where('uploaded_by', $user->id)
                    ->whereDate('created_at', $date->toDateString())
                    ->count(),
            ];
        });

        return view('dashboard.staff', compact(
            'myTotalUploads',
            'myThisMonth',
            'myRecentEos',
            'myRecentLogs',
            'myTotalActions',
            'myDownloads',
            'totalEos',
            'activeEos',
            'thisYearEos',
            'statusCounts',
            'needsAttention',
            'recentEos',
            'last7Days'
        ));
    }
}
