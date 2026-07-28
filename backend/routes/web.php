<?php

use App\Http\Controllers\InstallController;
use App\Http\Controllers\Web\AdminPanelController;
use App\Http\Middleware\AdminWebGuardMiddleware;
use App\Http\Middleware\InstallerGuardMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Web Installer — blocked after installation
Route::prefix('install')
    ->middleware(InstallerGuardMiddleware::class)
    ->group(function () {
        Route::get('/', [InstallController::class, 'show'])->name('install.show');
        Route::post('/test-database', [InstallController::class, 'testDatabase'])->name('install.test-db');
        Route::post('/save-database', [InstallController::class, 'saveDatabase'])->name('install.save-db');
        Route::post('/save-admin', [InstallController::class, 'saveAdmin'])->name('install.save-admin');
        Route::post('/execute', [InstallController::class, 'execute'])->name('install.execute');
    });

// Admin Panel — session-based login for managing API keys
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminPanelController::class, 'showLogin'])
        ->name('login')
        ->middleware('guest');
    Route::post('/login', [AdminPanelController::class, 'login'])
        ->name('login.post')
        ->middleware('guest');
    Route::get('/forgot-password', [AdminPanelController::class, 'showForgotPassword'])
        ->name('forgot-password')
        ->middleware('guest');
    Route::post('/forgot-password', [AdminPanelController::class, 'sendResetLink'])
        ->name('forgot-password.post')
        ->middleware('guest');
    Route::get('/reset-password', [AdminPanelController::class, 'showResetPassword'])
        ->name('reset-password')
        ->middleware('guest');
    Route::post('/reset-password', [AdminPanelController::class, 'resetPassword'])
        ->name('reset-password.post')
        ->middleware('guest');
    Route::post('/logout', [AdminPanelController::class, 'logout'])
        ->name('logout');

    Route::middleware(AdminWebGuardMiddleware::class)->group(function () {
        Route::get('/', [AdminPanelController::class, 'dashboard'])->name('dashboard');
        Route::get('/api-keys/create', [AdminPanelController::class, 'createKeyShow'])->name('keys.create');
        Route::post('/api-keys', [AdminPanelController::class, 'createKey'])->name('keys.store');
        Route::delete('/api-keys/{apiKey}', [AdminPanelController::class, 'destroyKey'])->name('keys.destroy');
    });
});
