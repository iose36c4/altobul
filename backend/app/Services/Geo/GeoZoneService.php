<?php

namespace App\Services\Geo;

use App\Models\GeoZone;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeoZoneService
{
    public function isInActiveZone(?Profile $profile): bool
    {
        if (! $profile || ! $profile->location) {
            Log::debug('GeoZoneService: profile or location missing', [
                'profile_id' => $profile?->getKey(),
            ]);

            return false;
        }

        $result = DB::selectOne(
            'SELECT EXISTS(
                SELECT 1 FROM geo_zones gz
                INNER JOIN geo_polygons gp ON gz.id = gp.zone_id
                WHERE gz.is_active = true
                AND ST_Within(
                    (SELECT location FROM profiles WHERE user_id = ?)::geometry,
                    gp.geom::geometry
                )
            ) as exists',
            [$profile->getKey()]
        );

        Log::debug('GeoZoneService: isInActiveZone', [
            'profile_id' => $profile->getKey(),
            'result' => $result->exists ?? false,
        ]);

        return $result->exists ?? false;
    }

    public function getActiveZoneForProfile(Profile $profile): ?GeoZone
    {
        if (! $profile->location) {
            return null;
        }

        return DB::selectOne(
            'SELECT gz.* FROM geo_zones gz
            INNER JOIN geo_polygons gp ON gz.id = gp.zone_id
            WHERE gz.is_active = true
            AND ST_Within(
                (SELECT location FROM profiles WHERE user_id = ?)::geometry,
                gp.geom::geometry
            )
            LIMIT 1',
            [$profile->getKey()]
        );
    }

    public function getDistance(Profile $from, Profile $to): float
    {
        if (! $from->location || ! $to->location) {
            return 0.0;
        }

        // Returns distance in meters
        $result = DB::selectOne(
            'SELECT ST_Distance(?, ?) as distance',
            [$from->location, $to->location]
        );

        return (float) ($result->distance ?? 0);
    }

    public function getDistanceKmRounded(Profile $from, Profile $to, int $precisionMeters = 1000): float
    {
        $distanceMeters = $this->getDistance($from, $to);

        if ($distanceMeters < $precisionMeters) {
            return round($distanceMeters / 1000, 1);
        }

        return round($distanceMeters / 1000);
    }

    public function getUsersInZone(GeoZone $zone, int $limit = 50)
    {
        return Profile::whereHas('geoPolygons', function ($q) use ($zone) {
            $q->whereRaw('ST_Within(profiles.location::geometry, geo_polygons.geom::geometry)')
                ->where('geo_polygons.zone_id', $zone->id);
        })->limit($limit)->get();
    }
}
