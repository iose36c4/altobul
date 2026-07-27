<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        'geom',
    ];

    protected $casts = [
        'geometry' => 'array',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected $appends = ['geom'];

    protected function getGeomAttribute()
    {
        return $this->attributes['geom'] ?? null;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::saving(function (self $polygon) {
            if ($polygon->geometry && ! $polygon->getAttribute('geom')) {
                $polygon->setAttribute('geom', DB::raw('ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)::geography', [json_encode($polygon->geometry)]));
            }
        });
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class, 'zone_id', 'id');
    }
}
