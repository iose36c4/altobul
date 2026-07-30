<?php

use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeoZoneController;
use App\Http\Controllers\Admin\InstallController;
use App\Http\Controllers\Admin\ProfileFieldController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Middleware\AdminWebGuardMiddleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

// Installer — only available when ADMIN_API_KEY is not set
Route::prefix('install')->name('install.')->withoutMiddleware([VerifyCsrfToken::class])->group(function () {
    Route::get('/', [InstallController::class, 'show'])->name('show');
    Route::post('/test', [InstallController::class, 'testConnection'])->name('test');
    Route::post('/save', [InstallController::class, 'save'])->name('save');
});

Route::get('/', [InstallController::class, 'redirectToInstall']);

// Auth Web
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
    });

    Route::middleware([AdminWebGuardMiddleware::class])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Geo Zonas
        Route::resource('geo-zones', GeoZoneController::class)
            ->parameters(['geo-zones' => 'zone']);
        Route::post('geo-zones/{zone}/polygons', [GeoZoneController::class, 'addPolygon'])->name('geo-zones.polygons.store');
        Route::put('geo-zones/{zone}/polygons/{polygon}', [GeoZoneController::class, 'updatePolygon'])->name('geo-zones.polygons.update');
        Route::delete('geo-zones/{zone}/polygons/{polygon}', [GeoZoneController::class, 'deletePolygon'])->name('geo-zones.polygons.destroy');

        // Usuarios
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('users/{user}/ban', [UserController::class, 'ban'])->name('users.ban');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/role', [UserController::class, 'changeRole'])->name('users.change-role');

        // Content Moderation
        Route::delete('users/{user}/posts/{post}', [UserController::class, 'deletePost'])->name('users.delete-post');
        Route::delete('users/{user}/photos/{photo}', [UserController::class, 'deletePhoto'])->name('users.delete-photo');
        Route::delete('users/{user}/tokes/{toke}', [UserController::class, 'deleteToke'])->name('users.delete-toke');
        Route::delete('users/{user}/matches/{match}', [UserController::class, 'deleteMatch'])->name('users.delete-match');
        Route::delete('users/{user}/friendships/{friendship}', [UserController::class, 'deleteFriendship'])->name('users.delete-friendship');
        Route::delete('users/{user}/conversations/{conversation}', [UserController::class, 'deleteConversation'])->name('users.delete-conversation');
        Route::delete('users/{user}/conversations/{conversation}/messages/{message}', [UserController::class, 'deleteMessage'])->name('users.delete-message');

        // Profile Fields
        Route::resource('profile-fields', ProfileFieldController::class)->except(['show']);
        Route::post('profile-fields/reorder', [ProfileFieldController::class, 'reorder'])->name('profile-fields.reorder');

        // Verificaciones
        Route::get('verifications', [VerificationController::class, 'index'])->name('verifications.index');
        Route::get('verifications/{verification}', [VerificationController::class, 'show'])->name('verifications.show');
        Route::post('verifications/{verification}/approve', [VerificationController::class, 'approve'])->name('verifications.approve');
        Route::post('verifications/{verification}/reject', [VerificationController::class, 'reject'])->name('verifications.reject');

        // Configuración
        Route::get('config', [ConfigController::class, 'index'])->name('config.index');
        Route::put('config', [ConfigController::class, 'update'])->name('config.update');

        // API Keys
        Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
        Route::get('api-keys/create', [ApiKeyController::class, 'create'])->name('api-keys.create');
        Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
        Route::get('api-keys/created', [ApiKeyController::class, 'showCreated'])->name('api-keys.show-created');
        Route::delete('api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');

        // Reportes / Denuncias
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::post('reports/{report}/dismiss', [ReportController::class, 'dismiss'])->name('reports.dismiss');
        Route::post('reports/{report}/action', [ReportController::class, 'action'])->name('reports.action');

        // Auditoría
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
