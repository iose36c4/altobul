<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PointCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        // PostGIS returns WKB, convert to array [lat, lng]
        if ($value === null) {
            return null;
        }

        // If it's already an array, return it
        if (is_array($value)) {
            return $value;
        }

        // Parse WKB or EWKB
        // For now return as-is since PostGIS driver handles this
        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        // Convert array [lat, lng] to PostGIS point
        if (is_array($value) && count($value) === 2) {
            return \DB::raw("ST_SetSRID(ST_MakePoint({$value[1]}, {$value[0]}), 4326)::geography");
        }

        // If it's already a valid point, return as-is
        return $value;
    }
}
