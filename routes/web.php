<?php

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExecutiveOrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Public root — redirect to the public portal for unauthenticated visitors ─

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('public.index');
});

// ─── Guest Routes ─────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // Password reset
    Route::get('/forgot-password',                [PasswordResetController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password',               [PasswordResetController::class, 'sendLink'])->name('password.email')->middleware('throttle:6,1');
    Route::get('/reset-password/{token}',         [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password',                [PasswordResetController::class, 'reset'])->name('password.update');
});

// ─── Public Portal (no auth required) ────────────────────────────────────────

Route::prefix('portal')->name('public.')->group(function () {
    Route::get('/',                                   [\App\Http\Controllers\PublicPortalController::class, 'index'])->name('index');
    Route::get('/{executiveOrder}',                   [\App\Http\Controllers\PublicPortalController::class, 'show'])->name('show');
    Route::get('/{executiveOrder}/pdf',               [\App\Http\Controllers\PublicPortalController::class, 'viewPdf'])->name('pdf');
    Route::get('/{executiveOrder}/download',          [\App\Http\Controllers\PublicPortalController::class, 'download'])->name('download');
});

// ─── Authenticated Routes ─────────────────────────────────────────────────────

Route::middleware(['auth', 'maintenance'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::get('/notifications',             [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read',  [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all',   [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // ── Profile ────────────────────────────────────────────────────────────────
    Route::get('/profile',              [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/info',       [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::patch('/profile/password',   [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::patch('/profile/signature',  [ProfileController::class, 'updateSignature'])->name('profile.update-signature');
    Route::post('/profile/avatar',      [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar',    [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');

    // ── Signature image serving (local disk, not public) ──────────────────────
    Route::get('/signatures/users/{user}',              [ProfileController::class, 'serveSignature'])->name('signature.user');
    Route::get('/signatures/eo/{executiveOrder}',       [\App\Http\Controllers\ExecutiveOrderController::class, 'serveSignature'])->name('signature.eo');

    // ── Executive Orders ───────────────────────────────────────────────────────
    Route::prefix('executive-orders')->name('executive-orders.')->group(function () {
        Route::get('/',               [ExecutiveOrderController::class, 'index'])->name('index');
        Route::get('/create',         [ExecutiveOrderController::class, 'create'])->name('create');
        Route::post('/',              [ExecutiveOrderController::class, 'store'])->name('store');
        Route::get('/export',         [\App\Http\Controllers\ExportController::class, 'exportCsv'])->name('export');

        // ── Archive routes (static segments — must come before wildcard routes) ──
        Route::middleware('role:admin')->group(function () {
            Route::get('/archive',               [ExecutiveOrderController::class, 'archive'])->name('archive');
            Route::post('/archive/{id}/restore', [ExecutiveOrderController::class, 'restore'])->name('restore');
            Route::delete('/archive/{id}',       [ExecutiveOrderController::class, 'forceDestroy'])->name('force-destroy');
        });

        // ── Wildcard routes (must come after static segments) ─────────────────
        Route::get('/{executiveOrder}',               [ExecutiveOrderController::class, 'show'])->name('show');
        Route::get('/{executiveOrder}/edit',          [ExecutiveOrderController::class, 'edit'])->name('edit');
        Route::put('/{executiveOrder}',               [ExecutiveOrderController::class, 'update'])->name('update');
        Route::get('/{executiveOrder}/pdf',           [ExecutiveOrderController::class, 'viewPdf'])->name('pdf');
        Route::get('/{executiveOrder}/download',      [ExecutiveOrderController::class, 'download'])->name('download');
        Route::get('/{executiveOrder}/chain',         [ExecutiveOrderController::class, 'amendmentChain'])->name('chain');
        Route::get('/{executiveOrder}/export',        [\App\Http\Controllers\ExportController::class, 'exportSingleCsv'])->name('export-single');
        Route::get('/{executiveOrder}/version-history',[ExecutiveOrderController::class, 'versionHistory'])->name('version-history');
        Route::get('/{executiveOrder}/version-history/download',[ExecutiveOrderController::class, 'downloadArchived'])->name('version-history.download');

        // Admin-only destroy
        Route::delete('/{executiveOrder}', [ExecutiveOrderController::class, 'destroy'])
            ->name('destroy')
            ->middleware('role:admin');
    });

    // ── Admin-Only Routes ──────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // User management
        Route::resource('users', UserController::class)->except(['show']);

        // Activity logs
        Route::get('logs', [LogController::class, 'index'])->name('logs.index');

        // Settings
        Route::get('settings',          [SettingsController::class, 'index'])->name('settings.index');
        Route::patch('settings',        [SettingsController::class, 'update'])->name('settings.update');
        Route::get('settings/health',   [SettingsController::class, 'health'])->name('settings.health');
    });
});
