<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasExpiration;

class Photo extends Model
{
    use HasExpiration;
    
    protected $table = 'photos';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'user_id',
        'storage_key',
        'mime_type',
        'width',
        'height',
        'size_bytes',
        'sort_order',
        'is_primary',
        'visibility',
        'requires_verified',
        'status',
        'deleted_at',
    ];
    
    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'size_bytes' => 'integer',
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
        'requires_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function grants(): HasMany
    {
        return $this->hasMany(PhotoAccess::class, 'photo_id', 'id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE')
                     ->whereNull('deleted_at');
    }
    
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE' && $this->deleted_at === null;
    }

    public function getUrl(): string
    {
        return config('filesystems.disks.s3.url', 'https://s3.amazonaws.com') . '/' . config('filesystems.disks.s3.bucket') . '/' . $this->storage_key;
    }
}