<?php

namespace App\Models;

use App\Traits\HasExpiration;
use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FriendshipRequest extends Model
{
    use HasExpiration, HasUuidPrimaryKey;

    protected $table = 'friendship_requests';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

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
