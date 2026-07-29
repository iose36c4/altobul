<?php

use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeoZoneController;
use App\Http\Controllers\Admin\InstallController;
use App\Http\Controllers\Admin\ProfileFieldController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Middleware\AdminWebGuardMiddleware;
use Illuminate\Support\Facades\Route;

// Installer — only available when ADMIN_API_KEY is not set
Route::prefix('install')->name('install.')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function () {
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
            ->parameters(['geo-zones' => 'zone'])
            ->except(['show']);
        Route::post('geo-zones/{zone}/polygons', [GeoZoneController::class, 'addPolygon'])->name('geo-zones.polygons.store');
        Route::put('geo-zones/{zone}/polygons/{polygon}', [GeoZoneController::class, 'updatePolygon'])->name('geo-zones.polygons.update');
        Route::delete('geo-zones/{zone}/polygons/{polygon}', [GeoZoneController::class, 'deletePolygon'])->name('geo-zones.polygons.destroy');

        // Usuarios
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('users/{user}/role', [UserController::class, 'changeRole'])->name('users.change-role');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');

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

        // Auditoría
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
