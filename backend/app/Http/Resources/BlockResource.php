<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'blocker_id' => $this->blocker_id,
            'blocked_id' => $this->blocked_id,
            'blocker' => new UserResource($this->whenLoaded('blocker')),
            'blocked' => new UserResource($this->whenLoaded('blocked')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
