<?php

namespace App\Http\Resources;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();

        $url = $this->resource instanceof Photo
            ? $this->resource->getUrl()
            : $this->getUrl();

        if ($viewer instanceof User && $this->resource instanceof Photo) {
            try {
                $url = $this->resource->getSignedUrl($viewer);
            } catch (\Throwable) {
                // Fall back to public URL if signing fails
            }
        }

        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'file_url' => $url,
            'mime_type' => $this->mime_type,
            'width' => $this->width,
            'height' => $this->height,
            'size_bytes' => $this->size_bytes,
            'sort_order' => $this->sort_order,
            'is_primary' => $this->is_primary,
            'visibility' => $this->visibility,
            'requires_verified' => $this->requires_verified,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
