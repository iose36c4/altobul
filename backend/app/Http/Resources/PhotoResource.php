<?php

namespace App\Http\Resources;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Photo $photo */
        $photo = $this->resource;

        return [
            'id' => $photo->id,
            'url' => $photo->getUrl(),
            'width' => $photo->width,
            'height' => $photo->height,
            'sort_order' => $photo->sort_order,
            'is_primary' => $photo->is_primary,
            'visibility' => $photo->visibility,
            'requires_verified' => $photo->requires_verified,
            'created_at' => $photo->created_at?->toISOString(),
        ];
    }
}
