<?php

namespace App\Models;

use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    use HasUuidPrimaryKey;

    protected $table = 'blocks';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id', 'id');
    }

    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id', 'id');
    }

    public static function existsBetween(User $a, User $b): bool
    {
        return static::where(function ($q) use ($a, $b) {
            $q->where('blocker_id', $a->id)->where('blocked_id', $b->id);
        })->orWhere(function ($q) use ($a, $b) {
            $q->where('blocker_id', $b->id)->where('blocked_id', $a->id);
        })->exists();
    }

    public function scopeByBlocker(Builder $query, string $blockerId): Builder
    {
        return $query->where('blocker_id', $blockerId);
    }

    public function scopeByBlocked(Builder $query, string $blockedId): Builder
    {
        return $query->where('blocked_id', $blockedId);
    }
}
