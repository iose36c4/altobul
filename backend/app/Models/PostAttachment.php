<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAttachment extends Model
{
    protected $table = 'post_attachments';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'post_id',
        'storage_key',
        'mime_type',
        'width',
        'height',
        'size_bytes',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'width' => 'integer',
        'height' => 'integer',
        'size_bytes' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }
}