<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ApiKeyResource;
use App\Models\ApiKey;
use App\Models\User;
use App\Services\Admin\AuditLogService;
use App\Services\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiKeyController extends Controller
{
    public function __construct(
        private ApiKeyService $apiKeyService,
        protected AuditLogService $auditLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $keys = ApiKey::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) $request->input('per_page', 20), 100));

        return response()->json([
            'api_keys' => ApiKeyResource::collection($keys),
            'pagination' => [
                'current_page' => $keys->currentPage(),
                'last_page' => $keys->lastPage(),
                'per_page' => $keys->perPage(),
                'total' => $keys->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:CLIENT,ADMIN,MOBILE,INTEGRATION'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->apiKeyService->createApiKey(
            $request->user(),
            $request->input('name'),
            $request->input('type'),
            $request->input('expires_in_days')
        );

        $this->auditLog->log('api_key.create', 'ApiKey', $result['api_key']->id, [
            'key_id' => $result['api_key']->id,
            'type' => $result['api_key']->type,
        ], $request->user(), $request);

        return response()->json([
            'api_key' => new ApiKeyResource($result['api_key']),
            'raw_key' => $result['raw_key'],
            'warning' => 'This is the only time the raw key will be shown. Store it securely.',
        ], 201);
    }

    public function show(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $apiKey->load('creator');

        return response()->json([
            'api_key' => new ApiKeyResource($apiKey),
        ]);
    }

    public function destroy(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $keyId = $apiKey->id;
        $keyType = $apiKey->type;

        $this->apiKeyService->revokeApiKey($apiKey);

        $this->auditLog->log('api_key.revoke', 'ApiKey', $keyId, [
            'key_id' => $keyId,
            'type' => $keyType,
        ], request()->user(), request());

        return response()->json([
            'message' => 'API key revoked successfully',
        ]);
    }

    private function authorizeAdmin(?User $user): void
    {
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Administrative privileges required');
        }
    }
}
