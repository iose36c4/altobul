<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoZone extends Model
{
    protected $table = 'geo_zones';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'created_by',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    
    public function polygons(): HasMany
    {
        return $this->hasMany(GeoPolygon::class, 'zone_id', 'id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}