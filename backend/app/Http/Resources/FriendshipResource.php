<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FriendshipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_a' => new UserResource($this->whenLoaded('userA')),
            'user_b' => new UserResource($this->whenLoaded('userB')),
            'status' => $this->status,
            'via_match' => $this->via_match,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
