<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoAccess extends Model
{
    protected $table = 'photo_access';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'photo_id',
        'grantee_id',
        'granted_by',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'photo_id', 'id');
    }

    public function grantee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grantee_id', 'id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by', 'id');
    }
}
