<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to mutating methods
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');

        if (! $key) {
            // Optional: reject requests without idempotency key for mutating operations
            // return response()->json(['error' => 'Idempotency-Key header required'], 400);
            return $next($request);
        }

        // Validate key format (UUID)
        if (! Str::isUuid($key)) {
            return response()->json(['error' => 'Invalid Idempotency-Key format'], 400);
        }

        // Include user/API key in cache key to prevent cross-user conflicts
        $userId = $request->user()?->id ?? $request->attributes->get('api_key_id') ?? 'anonymous';
        $cacheKey = "idempotency:{$userId}:{$key}";

        // Check if we have a cached response
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $response = response($cached['content'], $cached['status'])
                ->withHeaders($cached['headers']);
            $response->headers->set('X-Idempotency-Replay', 'true');

            return $response;
        }

        // Execute the request
        $response = $next($request);

        // Cache successful responses (2xx, 4xx but not 429)
        if ($response->isSuccessful() || ($response->status() >= 400 && $response->status() < 500 && $response->status() !== 429)) {
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'status' => $response->status(),
                'headers' => $response->headers->all(),
            ], now()->addHours(24));
        }

        return $response;
    }
}
