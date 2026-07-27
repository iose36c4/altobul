<?php

namespace App\Models;

use App\Traits\HasExpiration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMatch extends Model
{
    use HasExpiration;

    protected $table = 'matches';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_a_id',
        'user_b_id',
        'expires_at',
        'status',
        'ended_at',
        'ended_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => 'string',
    ];

    public function userA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_a_id', 'id');
    }

    public function userB(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_b_id', 'id');
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE')
            ->where('expires_at', '>', now());
    }

    public function scopeBetween($query, $userA, $userB)
    {
        $idA = $userA instanceof User ? $userA->id : $userA;
        $idB = $userB instanceof User ? $userB->id : $userB;
        $ids = [$idA, $idB];
        sort($ids);

        return $query->where('user_a_id', $ids[0])
            ->where('user_b_id', $ids[1]);
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE' && ! $this->isExpired();
    }
}
