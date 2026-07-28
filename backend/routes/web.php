<?php

use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Web Installer
Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'show'])->name('install.show');
    Route::post('/', [InstallController::class, 'install'])->name('install.post');
    Route::get('/success', function () {
        return redirect()->route('install.show');
    })->name('install.success');
});
