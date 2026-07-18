<?php

namespace App\Http\Controllers;

use App\Models\DocActivityLog;
use App\Models\Document;
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
        $totalDocs    = Document::count();
        $thisYearDocs = Document::whereYear('date_issued', date('Y'))->count();

        // ── Documents per year ────────────────────────────────────────────────
        $yearList = Document::selectRaw("strftime('%Y', date_issued) as year, COUNT(*) as count")
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        // ── Recent uploads (system-wide) ─────────────────────────────────────
        $recentDocs = Document::with('uploader')
            ->latest()
            ->take(5)
            ->get();

        // ── Recent activity (system-wide) ────────────────────────────────────
        $recentLogs = DocActivityLog::with(['user', 'document'])
            ->latest()
            ->take(10)
            ->get();

        // ── User stats ────────────────────────────────────────────────────────
        $totalUsers  = User::count();
        $adminCount  = User::where('role', 'admin')->count();
        $staffCount  = User::where('role', 'staff')->count();

        // ── System-wide metrics ───────────────────────────────────────────────
        $totalLogs     = DocActivityLog::count();
        $totalPdfViews = DocActivityLog::where('action', 'pdf_viewed')->count();

        // ── Most active users (last 30 days) ──────────────────────────────────
        $topUsers = DocActivityLog::select('user_id', DB::raw('COUNT(*) as action_count'))
            ->with('user')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('action_count')
            ->take(5)
            ->get();

        // ── Documents added in last 7 days ────────────────────────────────────
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->format('D'),
                'count' => Document::whereDate('created_at', $date->toDateString())->count(),
            ];
        });

        // ── New users this month ──────────────────────────────────────────────
        $newUsersThisMonth = User::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        // ── Document type breakdown ───────────────────────────────────────────
        $typeCounts = Document::selectRaw('document_type, COUNT(*) as count')
            ->groupBy('document_type')
            ->pluck('count', 'document_type')
            ->toArray();

        return view('dashboard.admin', compact(
            'totalDocs',
            'thisYearDocs',
            'typeCounts',
            'yearList',
            'recentDocs',
            'recentLogs',
            'totalUsers',
            'adminCount',
            'staffCount',
            'totalLogs',
            'totalPdfViews',
            'topUsers',
            'last7Days',
            'newUsersThisMonth'
        ));
    }

    // ─── Staff Dashboard ──────────────────────────────────────────────────────

    private function staffDashboard()
    {
        $user = auth()->user();

        // ── Personal document stats ───────────────────────────────────────────
        $myTotalUploads = Document::where('uploaded_by', $user->id)->count();
        $myThisMonth    = Document::where('uploaded_by', $user->id)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();
        $myRecentDocs   = Document::where('uploaded_by', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // ── Personal activity log ─────────────────────────────────────────────
        $myRecentLogs = DocActivityLog::with('document')
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();
        $myTotalActions = DocActivityLog::where('user_id', $user->id)->count();

        // ── Personal download count ───────────────────────────────────────────
        $myDownloads = DocActivityLog::where('user_id', $user->id)
            ->where('action', 'downloaded')
            ->count();

        // ── System-wide overview (read-only context) ──────────────────────────
        $totalDocs    = Document::count();
        $thisYearDocs = Document::whereYear('date_issued', date('Y'))->count();

        // ── Document type breakdown ───────────────────────────────────────────
        $typeCounts = Document::selectRaw('document_type, COUNT(*) as count')
            ->groupBy('document_type')
            ->pluck('count', 'document_type')
            ->toArray();

        // ── Recent documents across system (for browsing) ─────────────────────
        $recentDocs = Document::with('uploader')
            ->latest()
            ->take(5)
            ->get();

        // ── My uploads in last 7 days ─────────────────────────────────────────
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) use ($user) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->format('D'),
                'count' => Document::where('uploaded_by', $user->id)
                    ->whereDate('created_at', $date->toDateString())
                    ->count(),
            ];
        });

        return view('dashboard.staff', compact(
            'myTotalUploads',
            'myThisMonth',
            'myRecentDocs',
            'myRecentLogs',
            'myTotalActions',
            'myDownloads',
            'totalDocs',
            'thisYearDocs',
            'typeCounts',
            'recentDocs',
            'last7Days'
        ));
    }
}
