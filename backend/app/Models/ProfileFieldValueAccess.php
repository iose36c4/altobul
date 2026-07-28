<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileFieldValueAccess extends Model
{
    protected $table = 'profile_field_value_access';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'field_value_id',
        'grantee_id',
        'granted_by',
        'revoked_at',
        'expires_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function fieldValue(): BelongsTo
    {
        return $this->belongsTo(ProfileFieldValue::class, 'field_value_id', 'id');
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
