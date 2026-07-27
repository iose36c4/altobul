<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthorizationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            Log::debug('Admin Authorization - NO USER', ['path' => $request->path()]);

            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'User authentication required',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        if (! $user->isAdmin()) {
            Log::debug('Admin Authorization - NOT ADMIN', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'path' => $request->path(),
            ]);

            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Administrator privileges required',
                'code' => 'ADMIN_REQUIRED',
            ], 403);
        }

        Log::debug('Admin Authorization - SUCCESS', [
            'user_id' => $user->id,
            'path' => $request->path(),
        ]);

        return $next($request);
    }
}
