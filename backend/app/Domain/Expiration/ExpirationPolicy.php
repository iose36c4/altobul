<?php

namespace App\Domain\Expiration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ExpirationPolicy
{
    public static function isExpired(Model $model): bool
    {
        return $model->expires_at !== null
            && $model->expires_at->isPast();
    }

    public static function expiresAt(Model $model): ?Carbon
    {
        return $model->expires_at;
    }

    public static function ttlHours(string $resourceType): int
    {
        return match ($resourceType) {
            'toke' => config('app.toke_ttl_hours', 48),
            'match' => config('app.match_ttl_days', 7) * 24,
            'post' => config('app.post_ttl_hours', 24),
            'friendship_request' => config('app.friendship_request_ttl_hours', 168),
            default => 0,
        };
    }
}
