<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = 'api_keys';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'type',
        'key_hash',
        'key_prefix',
        'last_used_at',
        'expires_at',
        'revoked_at',
        'created_by',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'key_hash',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function isValid(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }

    public static function generateRawKey(): string
    {
        return 'alt_'.Str::random(48);
    }

    public static function hashKey(string $rawKey): string
    {
        return Hash::make($rawKey);
    }

    public static function extractPrefix(string $rawKey): string
    {
        return substr($rawKey, 0, 8);
    }

    public static function createKey(string $name, string $type, User $createdBy, ?string $expiresAt = null): array
    {
        $rawKey = self::generateRawKey();
        $keyHash = self::hashKey($rawKey);
        $prefix = self::extractPrefix($rawKey);

        $apiKey = self::create([
            'name' => $name,
            'type' => $type,
            'key_hash' => $keyHash,
            'key_prefix' => $prefix,
            'expires_at' => $expiresAt ? now()->parse($expiresAt) : null,
            'created_by' => $createdBy->id,
        ]);

        return [
            'api_key' => $apiKey,
            'raw_key' => $rawKey,
        ];
    }

    public static function findValidByRawKey(string $rawKey): ?self
    {
        $prefix = self::extractPrefix($rawKey);

        $apiKey = self::where('key_prefix', $prefix)
            ->first();

        if (! $apiKey || ! $apiKey->isValid()) {
            return null;
        }

        if (! Hash::check($rawKey, $apiKey->key_hash)) {
            return null;
        }

        return $apiKey;
    }
}
