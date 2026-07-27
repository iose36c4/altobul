<?php

namespace App\Services\Auth;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ApiKeyService
{
    public function createClientKey(string $name, User $createdBy, ?string $expiresAt = null): array
    {
        return ApiKey::createKey($name, 'CLIENT', $createdBy, $expiresAt);
    }

    public function createAdminKey(string $name, User $createdBy, ?string $expiresAt = null): array
    {
        return ApiKey::createKey($name, 'ADMIN', $createdBy, $expiresAt);
    }

    public function createMobileKey(string $name, User $createdBy, ?string $expiresAt = null): array
    {
        return ApiKey::createKey($name, 'MOBILE', $createdBy, $expiresAt);
    }

    public function createIntegrationKey(string $name, User $createdBy, ?string $expiresAt = null): array
    {
        return ApiKey::createKey($name, 'INTEGRATION', $createdBy, $expiresAt);
    }

    public function listKeys(User $createdBy): Collection
    {
        return ApiKey::where('created_by', $createdBy->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getKey(string $id, User $createdBy): ?ApiKey
    {
        return ApiKey::where('id', $id)
            ->where('created_by', $createdBy->id)
            ->first();
    }

    public function revokeKey(string $id, User $createdBy): bool
    {
        $key = ApiKey::where('id', $id)
            ->where('created_by', $createdBy->id)
            ->first();

        if (! $key) {
            return false;
        }

        $key->revoke();

        return true;
    }

    public function validateKey(string $rawKey): ?ApiKey
    {
        return ApiKey::findValidByRawKey($rawKey);
    }

    public function recordUsage(ApiKey $key): void
    {
        $key->recordUsage();
    }

    public function isClientKey(ApiKey $key): bool
    {
        return $key->type === 'CLIENT';
    }

    public function isAdminKey(ApiKey $key): bool
    {
        return $key->type === 'ADMIN';
    }

    public function isMobileKey(ApiKey $key): bool
    {
        return $key->type === 'MOBILE';
    }

    public function isIntegrationKey(ApiKey $key): bool
    {
        return $key->type === 'INTEGRATION';
    }
}
