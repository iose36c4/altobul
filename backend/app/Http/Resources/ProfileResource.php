<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Profile $profile */
        $profile = $this->resource;

        return [
            'user_id' => $profile->user_id,
            'title' => $profile->title,
            'description' => $profile->description,
            'birth_date' => $profile->birth_date?->format('Y-m-d'),
            'profile_visibility' => $profile->profile_visibility,
            'profile_requires_verified' => $profile->profile_requires_verified,
            'title_visibility' => $profile->title_visibility,
            'title_requires_verified' => $profile->title_requires_verified,
            'description_visibility' => $profile->description_visibility,
            'description_requires_verified' => $profile->description_requires_verified,
            'birth_date_visibility' => $profile->birth_date_visibility,
            'birth_date_requires_verified' => $profile->birth_date_requires_verified,
            'discoverable' => $profile->discoverable,
            'geo_zone_id' => $profile->geo_zone_id,
            'location_precision_meters' => $profile->location_precision_meters,
            'fields' => ProfileFieldValueResource::collection(
                $profile->fieldValues()->get()
            ),
            'created_at' => $profile->created_at?->toISOString(),
            'updated_at' => $profile->updated_at?->toISOString(),
        ];
    }
}
