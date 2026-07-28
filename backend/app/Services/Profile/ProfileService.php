<?php

namespace App\Services\Profile;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    public function updateFixedFields(User $user, array $data): Profile
    {
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        // Only allow specific fields to be updated
        $allowedFields = [
            'title', 'description', 'birth_date',
            'title_visibility', 'title_requires_verified',
            'description_visibility', 'description_requires_verified',
            'birth_date_visibility', 'birth_date_requires_verified',
            'profile_visibility', 'profile_requires_verified',
            'discoverable', 'geo_zone_id', 'location_precision_meters',
        ];

        $filtered = array_intersect_key($data, array_flip($allowedFields));
        $profile->fill($filtered)->save();

        return $profile->fresh();
    }

    public function updateLocation(User $user, array $data): Profile
    {
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        $lat = (float) $data['latitude'];
        $lng = (float) $data['longitude'];
        $precision = $data['precision_meters'] ?? config('app.location_default_precision_meters', 1000);

        DB::statement(
            'UPDATE profiles SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, location_precision_meters = ?, updated_at = ? WHERE user_id = ?',
            [$lng, $lat, $precision, now(), $user->id]
        );

        return $profile->fresh();
    }
}
