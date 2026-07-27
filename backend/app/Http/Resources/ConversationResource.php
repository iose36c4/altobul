<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_a' => new UserResource($this->whenLoaded('userA')),
            'user_b' => new UserResource($this->whenLoaded('userB')),
            'status' => $this->status,
            'ended_at' => $this->ended_at?->toISOString(),
            'ended_by' => $this->ended_by?->toISOString(),
            'last_message' => new MessageResource($this->whenLoaded('lastMessage')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
