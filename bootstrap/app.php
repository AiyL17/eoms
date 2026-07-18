<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // ── Daily backup at 2:00 AM ───────────────────────────────────────
        $schedule->command('backup:run')->dailyAt('02:00');

        // ── Clean up old backups at 3:00 AM (keeps rolling 7-day window) ──
        $schedule->command('backup:clean')->dailyAt('03:00');

        // ── Warn admins 5 days before archived documents are permanently purged ──
        $schedule->command('doc:notify-expiring')->dailyAt('03:30');

        // ── Permanently purge soft-deleted documents older than 30 days ───────
        $schedule->command('doc:prune-deleted')->dailyAt('04:00');

        // ── Warn all admins & staff of approaching document expiration dates ───
        $schedule->command('doc:notify-expiration-warning')->dailyAt('08:30');

        // ── Rebuild FTS5 search index nightly ─────────────────────────────────
        $schedule->command('doc:rebuild-search-index')->dailyAt('01:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'        => \App\Http\Middleware\EnsureRole::class,
            'maintenance' => \App\Http\Middleware\CheckMaintenance::class,
        ]);

        // Track last_seen_at for every authenticated web request.
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackLastSeen::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
