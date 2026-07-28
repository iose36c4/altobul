<?php

use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Web Installer
Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'show'])->name('install.show');
    Route::post('/test-database', [InstallController::class, 'testDatabase'])->name('install.test-db');
    Route::post('/save-database', [InstallController::class, 'saveDatabase'])->name('install.save-db');
    Route::post('/save-admin', [InstallController::class, 'saveAdmin'])->name('install.save-admin');
    Route::post('/save-api-keys', [InstallController::class, 'saveApiKeys'])->name('install.save-keys');
    Route::post('/execute', [InstallController::class, 'execute'])->name('install.execute');
});
