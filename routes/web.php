<?php

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Public root ──────────────────────────────────────────────────────────────

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
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
    Route::get('/signatures/users/{user}', [ProfileController::class, 'serveSignature'])->name('signature.user');

    // ── Documents ──────────────────────────────────────────────────────────────
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/',               [DocumentController::class, 'index'])->name('index');
        Route::get('/create',         [DocumentController::class, 'create'])->name('create');
        Route::post('/',              [DocumentController::class, 'store'])->name('store');
        Route::get('/export',         [\App\Http\Controllers\ExportController::class, 'exportCsv'])->name('export');

        // ── Archive routes (static segments — must come before wildcard routes) ──
        Route::middleware('role:admin')->group(function () {
            Route::get('/archive',               [DocumentController::class, 'archive'])->name('archive');
            Route::post('/archive/{id}/restore', [DocumentController::class, 'restore'])->name('restore');
            Route::delete('/archive/{id}',       [DocumentController::class, 'forceDestroy'])->name('force-destroy');
        });

        // ── Wildcard routes (must come after static segments) ─────────────────
        Route::get('/{document}',               [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit',          [DocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}',               [DocumentController::class, 'update'])->name('update');
        Route::get('/{document}/pdf',           [DocumentController::class, 'viewPdf'])->name('pdf');
        Route::get('/{document}/download',      [DocumentController::class, 'download'])->name('download');
        Route::get('/{document}/export',        [\App\Http\Controllers\ExportController::class, 'exportSingleCsv'])->name('export-single');
        Route::get('/{document}/version-history',[DocumentController::class, 'versionHistory'])->name('version-history');
        Route::get('/{document}/version-history/download',[DocumentController::class, 'downloadArchived'])->name('version-history.download');
        Route::patch('/{document}/toggle-type', [DocumentController::class, 'toggleType'])->name('toggle-type');

        // Admin-only destroy
        Route::delete('/{document}', [DocumentController::class, 'destroy'])
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
