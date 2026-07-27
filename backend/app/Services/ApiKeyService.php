<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function generateKey(string $prefix = 'ab'): string
    {
        $random = Str::random(32);

        return "{$prefix}_{$random}";
    }

    public function hashKey(string $key): string
    {
        return Hash::make($key);
    }

    public function verifyKey(string $key, string $hash): bool
    {
        return Hash::check($key, $hash);
    }

    public function createApiKey(
        User $creator,
        string $name,
        string $type,
        ?int $expiresInDays = null
    ): array {
        $rawKey = $this->generateKey($type === 'CLIENT' ? 'ab_cli' : ($type === 'ADMIN' ? 'ab_adm' : 'ab_'));
        $keyHash = $this->hashKey($rawKey);
        $prefix = substr($rawKey, 0, 8);

        $apiKey = ApiKey::create([
            'name' => $name,
            'type' => $type,
            'key_hash' => $keyHash,
            'key_prefix' => $prefix,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
            'created_by' => $creator->id,
        ]);

        return [
            'api_key' => $apiKey,
            'raw_key' => $rawKey,
        ];
    }

    public function revokeApiKey(ApiKey $apiKey): void
    {
        $apiKey->update(['revoked_at' => now()]);
    }

    public function getActiveKeys(string $type): Collection
    {
        return ApiKey::where('type', $type)
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllKeys(): Collection
    {
        return ApiKey::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
