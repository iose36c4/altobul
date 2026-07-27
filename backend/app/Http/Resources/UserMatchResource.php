<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserMatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_a' => new UserResource($this->whenLoaded('userA')),
            'user_b' => new UserResource($this->whenLoaded('userB')),
            'expires_at' => $this->expires_at?->toISOString(),
            'status' => $this->status,
            'ended_at' => $this->ended_at?->toISOString(),
            'ended_by' => $this->ended_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
