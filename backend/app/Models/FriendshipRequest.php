<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasExpiration;

class FriendshipRequest extends Model
{
    use HasExpiration;
    
    protected $table = 'friendship_requests';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'requester_id',
        'addressee_id',
        'status',
        'responded_at',
        'expires_at',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
        'status' => 'string',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id', 'id');
    }
    
    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id', 'id');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }
    
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }
}