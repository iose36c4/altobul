<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileFieldValueAccessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'field_value_id' => $this->field_value_id,
            'grantee' => $this->whenLoaded('grantee', function () {
                return [
                    'id' => $this->grantee->id,
                    'email' => $this->grantee->email,
                    'profile' => $this->grantee->profile ? [
                        'title' => $this->grantee->profile->title,
                        'description' => $this->grantee->profile->description,
                    ] : null,
                ];
            }),
            'granted_by' => $this->whenLoaded('grantedBy', function () {
                return [
                    'id' => $this->grantedBy->id,
                    'email' => $this->grantedBy->email,
                ];
            }),
            'granted_at' => $this->granted_at?->toISOString(),
            'revoked_at' => $this->revoked_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'is_active' => $this->revoked_at === null && ($this->expires_at === null || $this->expires_at->isFuture()),
        ];
    }
}
