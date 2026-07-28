<?php

namespace App\Http\Resources\Profile;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileFieldValueAccessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'field_value_id' => $this->field_value_id,
            'grantee' => new UserResource($this->whenLoaded('grantee')),
            'grantee_id' => $this->grantee_id,
            'revoked_at' => $this->revoked_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
