<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeoPolygon extends Model
{
    protected $table = 'geo_polygons';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'zone_id',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class, 'zone_id', 'id');
    }
    
    public function getGeometry(): ?string
    {
        // PostGIS geography stored as WKB in DB
        return $this->attributes['geom'] ?? null;
    }
}