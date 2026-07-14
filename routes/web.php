<?php

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExecutiveOrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Guest Routes ─────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ─── Authenticated Routes ─────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Root redirect
    Route::get('/', fn () => redirect()->route('dashboard'));

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

    // ── Executive Orders ───────────────────────────────────────────────────────
    Route::prefix('executive-orders')->name('executive-orders.')->group(function () {
        Route::get('/',               [ExecutiveOrderController::class, 'index'])->name('index');
        Route::get('/create',         [ExecutiveOrderController::class, 'create'])->name('create');
        Route::post('/',              [ExecutiveOrderController::class, 'store'])->name('store');
        Route::get('/{executiveOrder}',       [ExecutiveOrderController::class, 'show'])->name('show');
        Route::get('/{executiveOrder}/edit',  [ExecutiveOrderController::class, 'edit'])->name('edit');
        Route::put('/{executiveOrder}',       [ExecutiveOrderController::class, 'update'])->name('update');
        Route::get('/{executiveOrder}/pdf',   [ExecutiveOrderController::class, 'viewPdf'])->name('pdf');
        Route::get('/{executiveOrder}/download', [ExecutiveOrderController::class, 'download'])->name('download');

        // Admin-only
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
    });
});
