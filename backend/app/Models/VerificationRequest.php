<?php

namespace App\Models;

use App\Traits\HasExpiration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationRequest extends Model
{
    use HasExpiration;

    protected $table = 'verification_requests';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'status',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'verification_method',
        'external_reference',
    ];

    protected $casts = [
        'status' => 'string',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['PENDING', 'APPROVED']);
    }
}
