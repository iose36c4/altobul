<?php

use App\Http\Controllers\InstallController;
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
