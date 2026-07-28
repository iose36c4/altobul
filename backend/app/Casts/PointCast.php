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

        if (! is_string($value) || strlen($value) < 50) {
            return null;
        }

        $binary = hex2bin($value);
        if ($binary === false) {
            return null;
        }

        $byteOrder = ord($binary[0]);
        $isLittleEndian = $byteOrder === 1;
        $packFormat = $isLittleEndian ? 'E' : 'N';

        $type = unpack($isLittleEndian ? 'V' : 'N', substr($binary, 1, 4))[1];
        $hasSrid = ($type & 0x20000000) !== 0;
        $baseType = $type & 0x0FFFFFFF;

        if ($baseType !== 1) {
            return null;
        }

        $offset = 5;
        if ($hasSrid) {
            $offset += 4;
        }

        $lng = unpack($packFormat, substr($binary, $offset, 8))[1];
        $lat = unpack($packFormat, substr($binary, $offset + 8, 8))[1];

        return [$lat, $lng];
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_array($value) && count($value) === 2) {
            $lat = (float) $value[0];
            $lng = (float) $value[1];

            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                throw new \InvalidArgumentException('Invalid coordinates: lat must be -90..90, lng must be -180..180');
            }

            return \DB::raw('ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography')
                ->setBindings([$lng, $lat]);
        }

        return $value;
    }
}
