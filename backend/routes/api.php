<?php

use Illuminate\Support\Facades\Route;

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('refresh', [\App\Http\Controllers\AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::post('forgot-password', [\App\Http\Controllers\AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [\App\Http\Controllers\AuthController::class, 'resetPassword']);
    Route::get('verify-email/{id}/{hash}', [\App\Http\Controllers\AuthController::class, 'verifyEmail'])
        ->middleware(['auth:sanctum', 'signed'])
        ->name('verification.verify');
    Route::post('resend-verification', [\App\Http\Controllers\AuthController::class, 'resendVerificationEmail'])
        ->middleware('auth:sanctum');

    // Verification Request Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('verification/request', [\App\Http\Controllers\AuthController::class, 'requestVerification']);
        Route::get('verification/status', [\App\Http\Controllers\AuthController::class, 'getVerificationStatus']);
    });
});

// Profile Routes
Route::prefix('profile')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [\App\Http\Controllers\ProfileController::class, 'show']);
    Route::put('/', [\App\Http\Controllers\ProfileController::class, 'update']);
    Route::get('fields', [\App\Http\Controllers\ProfileController::class, 'listFields']);
    Route::get('fields/{slug}', [\App\Http\Controllers\ProfileController::class, 'getField']);
    Route::put('fields/{slug}', [\App\Http\Controllers\ProfileController::class, 'setField']);
    Route::delete('fields/{slug}', [\App\Http\Controllers\ProfileController::class, 'deleteField']);
});

// User Profile (public view)
Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('{user:id}', [\App\Http\Controllers\UserController::class, 'show']);
});

// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('config', [\App\Http\Controllers\Admin\ConfigController::class, 'show']);
    Route::put('config', [\App\Http\Controllers\Admin\ConfigController::class, 'update']);

    Route::apiResource('profile-fields', \App\Http\Controllers\Admin\ProfileFieldDefinitionController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('profile-fields/reorder', [\App\Http\Controllers\Admin\ProfileFieldDefinitionController::class, 'reorder']);

    // Verification review routes
    Route::get('verification-requests', [\App\Http\Controllers\Admin\VerificationController::class, 'index']);
    Route::get('verification-requests/{id}', [\App\Http\Controllers\Admin\VerificationController::class, 'show']);
    Route::post('verification-requests/{id}/approve', [\App\Http\Controllers\Admin\VerificationController::class, 'approve']);
    Route::post('verification-requests/{id}/reject', [\App\Http\Controllers\Admin\VerificationController::class, 'reject']);

    // GeoZone routes
    Route::apiResource('geo-zones', \App\Http\Controllers\Admin\GeoZoneController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('geo-zones/{zone}/polygons', [\App\Http\Controllers\Admin\GeoZoneController::class, 'addPolygon']);
    Route::put('geo-zones/{zone}/polygons/{polygon}', [\App\Http\Controllers\Admin\GeoZoneController::class, 'updatePolygon']);
    Route::delete('geo-zones/{zone}/polygons/{polygon}', [\App\Http\Controllers\Admin\GeoZoneController::class, 'deletePolygon']);
});

// Current User
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [\App\Http\Controllers\AuthController::class, 'me']);
});