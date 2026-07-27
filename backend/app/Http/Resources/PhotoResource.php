<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'file_url' => $this->storage_key,
            'visibility' => $this->visibility,
            'requires_verified' => $this->requires_verified,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
