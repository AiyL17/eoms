<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        $health   = $this->gatherHealthChecks();
        return view('admin.settings.index', compact('settings', 'health'));
    }

    // ─── Health Checks ────────────────────────────────────────────────────────

    private function gatherHealthChecks(): array
    {
        $checks = [];

        // 1. Database connectivity
        try {
            DB::connection()->getPdo();
            $totalDocs  = Document::withTrashed()->count();
            $totalUsers = User::count();
            $checks[] = [
                'label'  => 'Database',
                'status' => 'ok',
                'detail' => "{$totalDocs} document records · {$totalUsers} users",
                'hint'   => null,
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'label'  => 'Database',
                'status' => 'fail',
                'detail' => 'Cannot connect to database.',
                'hint'   => 'The system cannot reach its database. Try restarting XAMPP (stop then start Apache and MySQL) from the XAMPP Control Panel. If the problem persists, contact your IT support.',
            ];
        }

        // 2. Storage writable
        $storageOk = is_writable(storage_path('app'));
        $checks[] = [
            'label'  => 'Storage',
            'status' => $storageOk ? 'ok' : 'fail',
            'detail' => $storageOk ? 'Storage directory is writable.' : 'Storage directory is not writable.',
            'hint'   => $storageOk ? null : 'The system cannot save uploaded files. Right-click the "storage" folder inside the project directory, go to Properties → Security, and make sure the web server user (e.g. "Everyone" or "IUSR") has Write permission. Then re-check.',
        ];

        // 3. Public storage symlink
        $symlinkOk = file_exists(public_path('storage'));
        $checks[] = [
            'label'  => 'Storage Symlink',
            'status' => $symlinkOk ? 'ok' : 'warn',
            'detail' => $symlinkOk
                ? 'Public storage symlink is active.'
                : 'Run "php artisan storage:link" to create the symlink.',
            'hint'   => $symlinkOk ? null : 'Uploaded files and avatars may not display correctly. Open the XAMPP Shell (or any terminal), navigate to the project folder, and run: php artisan storage:link — then re-check here.',
        ];

        // 4. Orphaned document files (record exists but PDF missing from disk)
        try {
            $orphaned = Document::whereNotNull('pdf_path')
                ->get()
                ->filter(fn ($doc) => ! Storage::disk('local')->exists($doc->pdf_path))
                ->count();

            $checks[] = [
                'label'  => 'Document File Integrity',
                'status' => $orphaned === 0 ? 'ok' : 'warn',
                'detail' => $orphaned === 0
                    ? 'All document PDF files are present on disk.'
                    : "{$orphaned} document record(s) are missing their PDF file.",
                'hint'   => $orphaned === 0 ? null : "One or more documents have a record in the system but their PDF file is missing from the server. Go to Documents, find the affected entries (they will fail to open or download), and re-upload their PDF files by editing each one.",
            ];
        } catch (\Throwable) {
            $checks[] = [
                'label'  => 'Document File Integrity',
                'status' => 'warn',
                'detail' => 'Could not verify document file integrity.',
                'hint'   => 'The system could not scan for missing files. Try re-checking. If this keeps appearing, contact your IT support.',
            ];
        }

        // 5. Pending / failed queue jobs
        try {
            $pendingJobs = Schema::hasTable('jobs')
                ? DB::table('jobs')->count()
                : 0;
            $failedJobs = Schema::hasTable('failed_jobs')
                ? DB::table('failed_jobs')->count()
                : 0;

            $jobStatus = 'ok';
            $jobDetail = 'No pending or failed jobs.';
            $jobHint   = null;

            if ($failedJobs > 0) {
                $jobStatus = 'warn';
                $jobDetail = "{$failedJobs} failed job(s) need attention.";
                $jobHint   = 'Some background tasks (like sending notifications) did not complete. This usually means email is not configured or the mail server is unreachable. Check your mail settings in the .env file or contact your IT support to review the failed jobs.';
            } elseif ($pendingJobs > 0) {
                $jobDetail = "{$pendingJobs} job(s) pending in queue.";
            }

            $checks[] = [
                'label'  => 'Queue',
                'status' => $jobStatus,
                'detail' => $jobDetail,
                'hint'   => $jobHint,
            ];
        } catch (\Throwable) {
            $checks[] = [
                'label'  => 'Queue',
                'status' => 'warn',
                'detail' => 'Could not check queue status.',
                'hint'   => 'The system could not read the job queue. Try re-checking. If this keeps appearing, contact your IT support.',
            ];
        }

        // 6. Soft-deleted documents awaiting purge
        try {
            $trashed       = Document::onlyTrashed()->count();
            $retentionDays = (int) Setting::get('archive_retention_days', 30);
            $checks[] = [
                'label'  => 'Archive Queue',
                'status' => $trashed > 0 ? 'warn' : 'ok',
                'detail' => $trashed > 0
                    ? "{$trashed} archived document(s) pending permanent purge (after {$retentionDays}d)."
                    : 'No documents pending purge.',
                'hint'   => $trashed > 0
                    ? "These are documents that have been archived and are waiting to be permanently deleted after {$retentionDays} days. No action is needed — the system handles this automatically overnight. If you want to remove them sooner or recover them, go to the Archive page."
                    : null,
            ];
        } catch (\Throwable) {
            $checks[] = [
                'label'  => 'Archive Queue',
                'status' => 'warn',
                'detail' => 'Could not check archive queue.',
                'hint'   => 'Try re-checking. If this keeps appearing, contact your IT support.',
            ];
        }

        // 7. FTS5 Search Index
        try {
            if (\App\Services\DocSearchService::ftsAvailable()) {
                $indexCount = \Illuminate\Support\Facades\DB::table('doc_search_index')->count();
                $eoCount    = \App\Models\Document::count();
                $inSync     = abs($indexCount - $eoCount) <= 2;
                $checks[] = [
                    'label'  => 'Search Index (FTS5)',
                    'status' => $inSync ? 'ok' : 'warn',
                    'detail' => $inSync
                        ? "FTS5 index active · {$indexCount} entries in sync."
                        : "Index has {$indexCount} entries but there are {$eoCount} documents — may need rebuild.",
                    'hint'   => $inSync ? null : 'Run "php artisan doc:rebuild-search-index" from the project folder to re-sync the full-text search index.',
                ];
            } else {
                $checks[] = [
                    'label'  => 'Search Index (FTS5)',
                    'status' => 'warn',
                    'detail' => 'FTS5 index not available — using fallback LIKE search.',
                    'hint'   => 'Run "php artisan migrate" to create the FTS5 virtual table, then "php artisan doc:rebuild-search-index".',
                ];
            }
        } catch (\Throwable) {
            $checks[] = [
                'label'  => 'Search Index (FTS5)',
                'status' => 'warn',
                'detail' => 'Could not check FTS5 index status.',
                'hint'   => null,
            ];
        }

        return $checks;
    }

    public function health()
    {
        $health = $this->gatherHealthChecks();
        return response()->json([
            'health'    => $health,
            'checkedAt' => now()->format('g:i A') . ' · ' . now()->format('M j, Y'),
            'overall'   => collect($health)->contains('status', 'fail')
                ? 'fail'
                : (collect($health)->contains('status', 'warn') ? 'warn' : 'ok'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'archive_retention_days'  => 'required|integer|min:1|max:365',
            'staff_can_upload'        => 'nullable|boolean',
            'maintenance_mode'        => 'nullable|boolean',
        ]);

        // Checkboxes that are unchecked won't be present in the request — default to 0
        $validated['staff_can_upload'] = $request->boolean('staff_can_upload') ? '1' : '0';
        $validated['maintenance_mode'] = $request->boolean('maintenance_mode') ? '1' : '0';

        Setting::setMany($validated);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings saved successfully.');
    }
}
