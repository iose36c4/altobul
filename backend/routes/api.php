<?php

use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\ContentModerationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeoZoneController;
use App\Http\Controllers\Admin\ProfileFieldDefinitionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\Client\DiscoveryController;
use App\Http\Controllers\Client\PhotoGrantController;
use App\Http\Controllers\Client\PostGrantController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\FriendshipRequestController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Profile\ProfileFieldValueAccessController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TokeController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\InstallerGuardMiddleware;
use Illuminate\Support\Facades\Route;

// CLIENT API - Requires CLIENT API Key
Route::prefix('client')
    ->middleware(['api.key:CLIENT'])
    ->group(function () {
        // Public auth routes (no user token required)
        Route::prefix('auth')->group(function () {
            Route::post('register', [AuthController::class, 'register'])
                ->middleware('throttle:register');
            Route::post('login', [AuthController::class, 'login'])
                ->middleware('throttle:login');
            Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
                ->middleware('throttle:password-reset');
            Route::post('reset-password', [AuthController::class, 'resetPassword'])
                ->middleware('throttle:password-reset');

            // Email verification - only needs signed URL + API key, no auth token
            Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
                ->middleware('signed')
                ->name('verification.verify');
        });

        // Protected auth routes - require API Key + User Token
        Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
            Route::post('resend-verification', [AuthController::class, 'resendVerificationEmail']);

            Route::post('verification/request', [AuthController::class, 'requestVerification']);
            Route::get('verification/status', [AuthController::class, 'getVerificationStatus']);
        });

        // Profile Routes
        Route::prefix('profile')->middleware('auth:sanctum')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::put('/location', [ProfileController::class, 'updateLocation']);
            Route::get('fields', [ProfileController::class, 'listFields']);
            Route::get('fields/{slug}', [ProfileController::class, 'getField']);
            Route::put('fields/{slug}', [ProfileController::class, 'setField']);
            Route::delete('fields/{slug}', [ProfileController::class, 'deleteField']);

            // Profile Field Value Access (Grants)
            Route::prefix('fields/{fieldValue}/grants')->middleware('auth:sanctum')->group(function () {
                Route::get('/', [ProfileFieldValueAccessController::class, 'index']);
                Route::post('/', [ProfileFieldValueAccessController::class, 'store']);
                Route::delete('{grantee}', [ProfileFieldValueAccessController::class, 'destroy']);
            });
        });

        // User Profile (public view)
        Route::prefix('users')->middleware('auth:sanctum')->group(function () {
            Route::get('{user:id}', [UserController::class, 'show']);
        });

        // Tokes
        Route::middleware(['auth:sanctum', 'verified', 'throttle:api-sensitive'])->group(function () {
            Route::post('tokes', [TokeController::class, 'store']);
            Route::get('tokes', [TokeController::class, 'index']);
            Route::post('tokes/{toke}/consume', [TokeController::class, 'consume']);
            Route::delete('tokes/{toke}', [TokeController::class, 'cancel']);
        });

        // Matches
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('matches', [MatchController::class, 'index']);
            Route::post('matches/{match}/convert-to-friendship', [MatchController::class, 'convertToFriendship']);
        });

        // Friendships
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('friendships', [FriendshipController::class, 'index']);
            Route::post('friendships', [FriendshipController::class, 'store']);
            Route::delete('friendships/{friendship}', [FriendshipController::class, 'destroy']);
        });

        // Friendship Requests
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('friendship-requests', [FriendshipRequestController::class, 'index']);
            Route::post('friendship-requests', [FriendshipRequestController::class, 'store']);
            Route::post('friendship-requests/{friendshipRequest}/accept', [FriendshipRequestController::class, 'accept']);
            Route::delete('friendship-requests/{friendshipRequest}', [FriendshipRequestController::class, 'destroy']);
        });

        // Blocks
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('blocks', [BlockController::class, 'index']);
            Route::post('blocks', [BlockController::class, 'store']);
            Route::delete('blocks/{block}', [BlockController::class, 'destroy']);
        });

        // Conversations
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('conversations', [ConversationController::class, 'index']);
            Route::post('conversations', [ConversationController::class, 'store'])->middleware('throttle:api-sensitive');
            Route::get('conversations/{conversation}', [ConversationController::class, 'show']);
            Route::delete('conversations/{conversation}', [ConversationController::class, 'destroy']);
        });

        // Messages
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('conversations/{conversation}/messages', [MessageController::class, 'index']);
            Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->middleware('throttle:api-messages');
        });

        // Photos
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('photos', [PhotoController::class, 'index']);
            Route::post('photos', [PhotoController::class, 'store'])->middleware('throttle:api-sensitive');
            Route::get('photos/{photo}', [PhotoController::class, 'show']);
            Route::delete('photos/{photo}', [PhotoController::class, 'destroy']);

            // Photo Grants (for PRIVATE photos)
            Route::prefix('photos/{photo}/grants')->group(function () {
                Route::get('/', [PhotoGrantController::class, 'index']);
                Route::post('/', [PhotoGrantController::class, 'store']);
                Route::delete('{grantee}', [PhotoGrantController::class, 'destroy']);
            });
        });

        // Discovery
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('discover', [DiscoveryController::class, 'index']);
            Route::get('discover/online', [DiscoveryController::class, 'online']);
            Route::get('discover/recent', [DiscoveryController::class, 'recent']);
            Route::get('discover/nearby', [DiscoveryController::class, 'nearby']);
        });

        // Reports
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::post('reports', [ReportController::class, 'store'])->middleware('throttle:api-sensitive');
        });

        // Posts
        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('posts', [PostController::class, 'index']);
            Route::post('posts', [PostController::class, 'store'])->middleware('throttle:api-sensitive');
            Route::get('posts/{post}', [PostController::class, 'show']);
            Route::delete('posts/{post}', [PostController::class, 'destroy']);

            // Post Grants (for PRIVATE posts)
            Route::prefix('posts/{post}/grants')->group(function () {
                Route::get('/', [PostGrantController::class, 'index']);
                Route::post('/', [PostGrantController::class, 'store']);
                Route::delete('{grantee}', [PostGrantController::class, 'destroy']);
            });
        });
    });

// ADMIN API - Requires ADMIN API Key
Route::prefix('admin')
    ->middleware(['api.key:ADMIN'])
    ->group(function () {
        // Public auth routes (no user token required)
        Route::prefix('auth')->group(function () {
            Route::post('register', [AuthController::class, 'register'])
                ->middleware('throttle:register');
            Route::post('login', [AuthController::class, 'login'])
                ->middleware('throttle:login');
            Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
                ->middleware('throttle:password-reset');
            Route::post('reset-password', [AuthController::class, 'resetPassword'])
                ->middleware('throttle:password-reset');

            // Email verification - only needs signed URL + API key, no auth token
            Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
                ->middleware('signed')
                ->name('admin.verification.verify');
        });

        // Protected auth routes - require API Key + User Token
        Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
            Route::post('resend-verification', [AuthController::class, 'resendVerificationEmail']);

            Route::post('verification/request', [AuthController::class, 'requestVerification']);
            Route::get('verification/status', [AuthController::class, 'getVerificationStatus']);
        });

        // Admin API Key verification - only requires API Key
        Route::get('verify', function () {
            return response()->json(['success' => true, 'message' => 'API key is valid']);
        });

        // Admin Configuration - requires admin API key (admin panel uses API key auth)
        // The 'api.key:ADMIN' middleware on the group already validates the admin API key
        Route::group([], function () {
            // Dashboard
            Route::get('dashboard/metrics', [DashboardController::class, 'metrics']);
            Route::get('dashboard/charts', [DashboardController::class, 'charts']);

            // API Key Management
            Route::get('api-keys', [ApiKeyController::class, 'index']);
            Route::post('api-keys', [ApiKeyController::class, 'store']);
            Route::get('api-keys/{apiKey}', [ApiKeyController::class, 'show']);
            Route::delete('api-keys/{apiKey}', [ApiKeyController::class, 'destroy']);

            // Profile Field Definitions
            Route::apiResource('profile-fields', ProfileFieldDefinitionController::class)
                ->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::post('profile-fields/reorder', [ProfileFieldDefinitionController::class, 'reorder']);

            // Verification Review
            Route::get('verification-requests', [VerificationController::class, 'index']);
            Route::get('verification-requests/{verificationRequest}', [VerificationController::class, 'show']);
            Route::post('verification-requests/{verificationRequest}/approve', [VerificationController::class, 'approve']);
            Route::post('verification-requests/{verificationRequest}/reject', [VerificationController::class, 'reject']);

            // Geo Zones
            Route::apiResource('geo-zones', GeoZoneController::class)
                ->parameters(['geo-zones' => 'zone'])
                ->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::post('geo-zones/merge', [GeoZoneController::class, 'merge']);
            Route::post('geo-zones/subtract', [GeoZoneController::class, 'subtract']);
            Route::post('geo-zones/{zone}/polygons', [GeoZoneController::class, 'addPolygon']);
            Route::put('geo-zones/{zone}/polygons/{polygon}', [GeoZoneController::class, 'updatePolygon']);
            Route::delete('geo-zones/{zone}/polygons/{polygon}', [GeoZoneController::class, 'deletePolygon']);

            // User Management
            Route::get('users', [AdminUserController::class, 'index']);
            Route::post('users', [AdminUserController::class, 'store']);
            Route::get('users/{user}', [AdminUserController::class, 'show']);
            Route::put('users/{user}', [AdminUserController::class, 'update']);
            Route::post('users/{user}/suspend', [AdminUserController::class, 'suspend']);
            Route::post('users/{user}/activate', [AdminUserController::class, 'activate']);
            Route::post('users/{user}/ban', [AdminUserController::class, 'ban']);
            Route::delete('users/{user}', [AdminUserController::class, 'destroy']);
            Route::post('users/{user}/change-role', [AdminUserController::class, 'changeRole']);

            // Config Management
            Route::get('config', [ConfigController::class, 'index']);
            Route::put('config', [ConfigController::class, 'update']);

            // Reports
            Route::get('reports', [App\Http\Controllers\Admin\ReportController::class, 'index']);
            Route::get('reports/{report}', [App\Http\Controllers\Admin\ReportController::class, 'show']);
            Route::post('reports/{report}/dismiss', [App\Http\Controllers\Admin\ReportController::class, 'dismiss']);
            Route::post('reports/{report}/action', [App\Http\Controllers\Admin\ReportController::class, 'action']);

            // Content Moderation
            Route::prefix('moderation')->group(function () {
                // Posts
                Route::get('users/{user}/posts', [ContentModerationController::class, 'userPosts']);
                Route::delete('posts/{post}', [ContentModerationController::class, 'deletePost']);

                // Photos
                Route::get('users/{user}/photos', [ContentModerationController::class, 'userPhotos']);
                Route::delete('photos/{photo}', [ContentModerationController::class, 'deletePhoto']);

                // Tokes
                Route::get('users/{user}/tokes', [ContentModerationController::class, 'userTokes']);
                Route::delete('tokes/{toke}', [ContentModerationController::class, 'deleteToke']);

                // Matches
                Route::get('users/{user}/matches', [ContentModerationController::class, 'userMatches']);
                Route::delete('matches/{match}', [ContentModerationController::class, 'deleteMatch']);

                // Friendships
                Route::get('users/{user}/friendships', [ContentModerationController::class, 'userFriendships']);
                Route::delete('friendships/{friendship}', [ContentModerationController::class, 'deleteFriendship']);

                // Conversations
                Route::get('users/{user}/conversations', [ContentModerationController::class, 'userConversations']);
                Route::get('conversations/{conversation}/messages', [ContentModerationController::class, 'conversationMessages']);
                Route::delete('conversations/{conversation}', [ContentModerationController::class, 'deleteConversation']);
                Route::delete('messages/{message}', [ContentModerationController::class, 'deleteMessage']);
            });

            // Audit Logs
            Route::get('audit-logs', [AuditLogController::class, 'index']);
        });
    });

// Backend Installer Routes (for initial setup)
Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'show']);
    Route::post('/', [InstallController::class, 'install'])
        ->middleware(['throttle:install', InstallerGuardMiddleware::class]);
    Route::get('status', [InstallController::class, 'status']);
});

// System Health & Compatibility (no API key required)
Route::prefix('system')->group(function () {
    Route::get('health', [SystemController::class, 'health']);
    Route::get('compatibility', [SystemController::class, 'compatibility']);
});
