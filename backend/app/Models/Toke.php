<?php

namespace App\Models;

use App\Traits\HasExpiration;
use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Toke extends Model
{
    use HasExpiration, HasUuidPrimaryKey;

    protected $table = 'tokes';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'expires_at',
        'status',
        'matched_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'matched_at' => 'datetime',
        'status' => 'string',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE')
            ->where('expires_at', '>', now());
    }

    public function isMutual(): bool
    {
        return $this->status === 'CONSUMED' && $this->matched_at !== null;
    }
}
