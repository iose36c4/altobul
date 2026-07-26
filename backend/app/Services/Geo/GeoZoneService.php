<?php

namespace App\Services\Geo;

use App\Models\GeoZone;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Builder;

class GeoZoneService
{
    public function isInActiveZone(?Profile $profile): bool
    {
        if (!$profile || !$profile->location) {
            return false;
        }
        
        return GeoZone::active()
            ->whereHas('polygons', function (Builder $q) use ($profile) {
                $q->whereRaw('ST_Within(?, geo_polygons.geom)', [$profile->location]);
            })
            ->exists();
    }
    
    public function getActiveZoneForProfile(Profile $profile): ?GeoZone
    {
        if (!$profile->location) {
            return null;
        }
        
        return GeoZone::active()
            ->whereHas('polygons', function (Builder $q) use ($profile) {
                $q->whereRaw('ST_Within(?, geo_polygons.geom)', [$profile->location]);
            })
            ->first();
    }
    
    public function getDistance(Profile $from, Profile $to): float
    {
        if (!$from->location || !$to->location) {
            return 0.0;
        }
        
        // Returns distance in meters
        $result = \DB::selectOne(
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
        return Profile::whereHas('geoPolygons', function (Builder $q) use ($zone) {
            $q->whereRaw('ST_Within(profiles.location, geo_polygons.geom)')
              ->where('geo_polygons.zone_id', $zone->id);
        })->limit($limit)->get();
    }
}