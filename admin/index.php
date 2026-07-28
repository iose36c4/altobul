<?php

declare(strict_types=1);

require_once __DIR__ . '/src/Router.php';
require_once __DIR__ . '/src/ApiClient.php';
require_once __DIR__ . '/src/Middleware/InstallerMiddleware.php';
require_once __DIR__ . '/src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/src/Controllers/BaseController.php';
require_once __DIR__ . '/src/Controllers/InstallController.php';
require_once __DIR__ . '/src/Controllers/DashboardController.php';
require_once __DIR__ . '/src/Controllers/ApiKeyController.php';
require_once __DIR__ . '/src/Controllers/UserController.php';
require_once __DIR__ . '/src/Controllers/GeoZoneController.php';
require_once __DIR__ . '/src/Controllers/ProfileFieldController.php';
require_once __DIR__ . '/src/Controllers/VerificationController.php';

session_start();

$router = new Router();

$router->get('/install', [App\Http\Controllers\InstallController::class, 'show']);
$router->post('/install/test', [App\Http\Controllers\InstallController::class, 'testConnection']);
$router->post('/install/save', [App\Http\Controllers\InstallController::class, 'save']);

$router->get('/login', [App\Http\Controllers\DashboardController::class, 'loginForm']);
$router->post('/login', [App\Http\Controllers\DashboardController::class, 'login']);
$router->post('/logout', [App\Http\Controllers\DashboardController::class, 'logout']);

$router->get('/', [App\Http\Controllers\DashboardController::class, 'index']);
$router->get('/api-keys', [App\Http\Controllers\ApiKeyController::class, 'index']);
$router->get('/api-keys/create', [App\Http\Controllers\ApiKeyController::class, 'create']);
$router->post('/api-keys', [App\Http\Controllers\ApiKeyController::class, 'store']);
$router->get('/api-keys/created', [App\Http\Controllers\ApiKeyController::class, 'created']);
$router->post('/api-keys/{id}/revoke', [App\Http\Controllers\ApiKeyController::class, 'revoke']);
$router->get('/users', [App\Http\Controllers\UserController::class, 'index']);
$router->get('/users/{id}', [App\Http\Controllers\UserController::class, 'show']);
$router->post('/users/{id}/suspend', [App\Http\Controllers\UserController::class, 'suspend']);
$router->post('/users/{id}/activate', [App\Http\Controllers\UserController::class, 'activate']);
$router->post('/users/{id}/role', [App\Http\Controllers\UserController::class, 'changeRole']);
$router->get('/geo-zones', [App\Http\Controllers\GeoZoneController::class, 'index']);
$router->get('/geo-zones/{id}', [App\Http\Controllers\GeoZoneController::class, 'show']);
$router->post('/geo-zones', [App\Http\Controllers\GeoZoneController::class, 'store']);
$router->put('/geo-zones/{id}', [App\Http\Controllers\GeoZoneController::class, 'update']);
$router->post('/geo-zones/{id}/delete', [App\Http\Controllers\GeoZoneController::class, 'destroy']);
$router->get('/profile-fields', [App\Http\Controllers\ProfileFieldController::class, 'index']);
$router->get('/profile-fields/{id}', [App\Http\Controllers\ProfileFieldController::class, 'show']);
$router->post('/profile-fields', [App\Http\Controllers\ProfileFieldController::class, 'store']);
$router->post('/profile-fields/{id}/delete', [App\Http\Controllers\ProfileFieldController::class, 'destroy']);
$router->get('/verifications', [App\Http\Controllers\VerificationController::class, 'index']);
$router->get('/verifications/{id}', [App\Http\Controllers\VerificationController::class, 'show']);
$router->post('/verifications/{id}/approve', [App\Http\Controllers\VerificationController::class, 'approve']);
$router->post('/verifications/{id}/reject', [App\Http\Controllers\VerificationController::class, 'reject']);

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
