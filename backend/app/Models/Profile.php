<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $table = 'profiles';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'title',
        'title_visibility',
        'title_requires_verified',
        'description',
        'description_visibility',
        'description_requires_verified',
        'birth_date',
        'birth_date_visibility',
        'birth_date_requires_verified',
        'profile_visibility',
        'profile_requires_verified',
        'location_precision_meters',
        'location',
        'discoverable',
        'geo_zone_id',
    ];
    
    protected $casts = [
        'birth_date' => 'date',
        'location_precision_meters' => 'integer',
        'location' => 'point',
        'discoverable' => 'boolean',
        'title_visibility' => 'string',
        'title_requires_verified' => 'boolean',
        'description_visibility' => 'string',
        'description_requires_verified' => 'boolean',
        'birth_date_visibility' => 'string',
        'birth_date_requires_verified' => 'boolean',
        'profile_visibility' => 'string',
        'profile_requires_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function fieldValues(): HasMany
    {
        return $this->hasMany(ProfileFieldValue::class, 'profile_id', 'user_id');
    }
    
    public function geoZone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class, 'geo_zone_id', 'id');
    }
}