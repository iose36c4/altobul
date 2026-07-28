<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next, string $expectedType): Response
    {
        $apiKeyHeader = $request->header('X-API-Key');

        Log::debug('API Key Middleware START', [
            'expected_type' => $expectedType,
            'path' => $request->path(),
        ]);

        if (! $apiKeyHeader) {
            Log::debug('API Key Middleware - MISSING KEY');

            return response()->json([
                'error' => 'API key required',
                'message' => 'X-API-Key header is missing',
                'code' => 'MISSING_API_KEY',
            ], 401);
        }

        if (strlen($apiKeyHeader) < 8) {
            Log::debug('API Key Middleware - INVALID FORMAT');

            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'API key format is invalid',
                'code' => 'INVALID_API_KEY_FORMAT',
            ], 401);
        }

        $prefix = substr($apiKeyHeader, 0, 8);

        Log::debug('API Key Middleware - LOOKING UP');

        // First check if key exists at all (regardless of type)
        $apiKey = ApiKey::where('key_prefix', $prefix)->first();

        Log::debug('API Key Middleware - LOOKUP RESULT', [
            'found' => $apiKey ? true : false,
        ]);

        if (! $apiKey) {
            Log::debug('API Key Middleware - NOT FOUND');

            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'API key not found',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        // CRITICAL: Verify the full API key hash cryptographically
        if (! Hash::check($apiKeyHeader, $apiKey->key_hash)) {
            Log::debug('API Key Middleware - HASH MISMATCH');

            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'API key is not valid',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        // Check type match
        if ($apiKey->type !== $expectedType) {
            Log::debug('API Key Middleware - TYPE MISMATCH');

            return response()->json([
                'error' => 'API key type mismatch',
                'message' => 'API key is not authorized for this endpoint',
                'code' => 'API_KEY_TYPE_MISMATCH',
            ], 403);
        }

        if (! $apiKey->isValid()) {
            if ($apiKey->isRevoked()) {
                Log::debug('API Key Middleware - REVOKED');

                return response()->json([
                    'error' => 'API key revoked',
                    'message' => 'This API key has been revoked',
                    'code' => 'API_KEY_REVOKED',
                ], 401);
            }

            if ($apiKey->isExpired()) {
                Log::debug('API Key Middleware - EXPIRED');

                return response()->json([
                    'error' => 'API key expired',
                    'message' => 'This API key has expired',
                    'code' => 'API_KEY_EXPIRED',
                ], 401);
            }

            Log::debug('API Key Middleware - INVALID');

            return response()->json([
                'error' => 'API key invalid',
                'message' => 'This API key is not valid',
                'code' => 'API_KEY_INVALID',
            ], 401);
        }

        Log::debug('API Key Middleware - SUCCESS');
        $apiKey->recordUsage();

        $request->attributes->set('api_key', $apiKey);
        $request->attributes->set('api_key_type', $expectedType);

        return $next($request);
    }
}
