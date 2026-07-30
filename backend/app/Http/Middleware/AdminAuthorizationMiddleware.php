<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthorizationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'User authentication required',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        if (! $user->isAdmin()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Administrator privileges required',
                'code' => 'ADMIN_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
