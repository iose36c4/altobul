<?php

namespace App\Models;

use App\Traits\HasExpiration;
use App\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    use HasExpiration, HasUuidPrimaryKey;

    protected $table = 'posts';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'content_md',
        'content_html',
        'visibility',
        'requires_verified',
        'expires_at',
        'status',
        'deleted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'expires_at' => 'datetime',
        'requires_verified' => 'boolean',
        'visibility' => 'string',
        'status' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attachment(): HasOne
    {
        return $this->hasOne(PostAttachment::class, 'post_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE')
            ->where('expires_at', '>', now())
            ->whereNull('deleted_at');
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE' && ! $this->isExpired() && $this->deleted_at === null;
    }
}
