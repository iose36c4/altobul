<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PointCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_array($value) && count($value) === 2) {
            $lat = (float) $value[0];
            $lng = (float) $value[1];

            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                throw new \InvalidArgumentException('Invalid coordinates: lat must be -90..90, lng must be -180..180');
            }

            return \DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        }

        return $value;
    }
}
