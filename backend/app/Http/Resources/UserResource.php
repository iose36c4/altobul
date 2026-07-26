<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at?->toISOString(),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'last_seen_at' => $this->last_seen_at?->toISOString(),
            'is_online' => $this->isOnline(),
            'is_verified' => $this->isVerified(),
            'created_at' => $this->created_at?->toISOString(),
            'profile' => $this->whenLoaded('profile', fn() => new ProfileResource($this->profile)),
        ];
    }
}