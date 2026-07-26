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
        'name',
        'geometry',
        'sort_order',
    ];
    
    protected $casts = [
        'geometry' => 'array',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class, 'zone_id', 'id');
    }
}