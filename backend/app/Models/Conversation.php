<?php

namespace App\Models;

use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasUuidPrimaryKey;

    protected $table = 'conversations';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_a_id',
        'user_b_id',
        'status',
        'ended_at',
        'ended_by',
        'ended_by_block',
    ];

    protected $casts = [
        'ended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'ended_by_block' => 'boolean',
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

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id')
            ->orderBy('created_at', 'desc');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id', 'id')
            ->latestOfMany('created_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
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
        return $this->status === 'ACTIVE';
    }

    public function hasParticipant(User|string $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->user_a_id === $userId || $this->user_b_id === $userId;
    }

    public function getOtherUser(User|string $user): ?User
    {
        $userId = $user instanceof User ? $user->id : $user;

        if ($this->user_a_id === $userId) {
            return $this->userB;
        }

        if ($this->user_b_id === $userId) {
            return $this->userA;
        }

        return null;
    }
}
