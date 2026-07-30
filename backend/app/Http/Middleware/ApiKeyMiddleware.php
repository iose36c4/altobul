<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next, string $expectedType): Response
    {
        $apiKeyHeader = $request->header('X-API-Key');

        if (! $apiKeyHeader) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'X-API-Key header is missing',
                'code' => 'MISSING_API_KEY',
            ], 401);
        }

        if (strlen($apiKeyHeader) < 8) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'API key format is invalid',
                'code' => 'INVALID_API_KEY_FORMAT',
            ], 401);
        }

        $prefix = substr($apiKeyHeader, 0, 8);

        $apiKey = ApiKey::where('key_prefix', $prefix)->first();

        if (! $apiKey) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'API key not found',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        if (! Hash::check($apiKeyHeader, $apiKey->key_hash)) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'API key is not valid',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        if ($apiKey->type !== $expectedType) {
            return response()->json([
                'error' => 'API key type mismatch',
                'message' => 'API key is not authorized for this endpoint',
                'code' => 'API_KEY_TYPE_MISMATCH',
            ], 403);
        }

        if (! $apiKey->isValid()) {
            if ($apiKey->isRevoked()) {
                return response()->json([
                    'error' => 'API key revoked',
                    'message' => 'This API key has been revoked',
                    'code' => 'API_KEY_REVOKED',
                ], 401);
            }

            if ($apiKey->isExpired()) {
                return response()->json([
                    'error' => 'API key expired',
                    'message' => 'This API key has expired',
                    'code' => 'API_KEY_EXPIRED',
                ], 401);
            }

            return response()->json([
                'error' => 'API key invalid',
                'message' => 'This API key is not valid',
                'code' => 'API_KEY_INVALID',
            ], 401);
        }

        $apiKey->recordUsage();

        $request->attributes->set('api_key', $apiKey);
        $request->attributes->set('api_key_type', $expectedType);

        return $next($request);
    }
}
