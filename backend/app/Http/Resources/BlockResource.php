<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'blocked' => new UserResource($this->whenLoaded('blocked')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
