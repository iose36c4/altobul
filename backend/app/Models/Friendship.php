<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasExpiration;

class Friendship extends Model
{
    use HasExpiration;
    
    protected $table = 'friendships';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'user_a_id',
        'user_b_id',
        'status',
        'ended_at',
        'ended_by',
        'via_match',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => 'string',
        'via_match' => 'boolean',
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
}