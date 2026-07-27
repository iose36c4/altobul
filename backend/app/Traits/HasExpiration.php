<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasExpiration
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE')
            ->where('expires_at', '<=', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->diffInMinutes(now()) < 60;
    }
}
