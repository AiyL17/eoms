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

        // ── Warn admins 5 days before archived EOs are permanently purged ──
        $schedule->command('eo:notify-expiring')->dailyAt('03:30');

        // ── Permanently purge soft-deleted EOs older than 30 days ─────────
        $schedule->command('eo:prune-deleted')->dailyAt('04:00');

        // ── Annual review reminders for active EOs ────────────────────────
        $schedule->command('eo:notify-review-due')->dailyAt('08:00');

        // ── Rebuild FTS5 search index nightly ─────────────────────────────
        $schedule->command('eo:rebuild-search-index')->dailyAt('01:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'        => \App\Http\Middleware\EnsureRole::class,
            'maintenance' => \App\Http\Middleware\CheckMaintenance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
