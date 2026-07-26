<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
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
    
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
    
    public function scopeBetween($query, $userA, $userB)
    {
        $ids = [$userA, $userB];
        sort($ids);
        return $query->where('user_a_id', $ids[0])
                     ->where('user_b_id', $ids[1]);
    }
    
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
}