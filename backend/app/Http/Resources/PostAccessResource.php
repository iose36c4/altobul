<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostAccessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'grantee' => [
                'id' => $this->grantee->id,
                'name' => $this->grantee->name,
            ],
            'granted_by' => [
                'id' => $this->grantedBy->id,
                'name' => $this->grantedBy->name,
            ],
            'granted_at' => $this->granted_at?->toISOString(),
            'revoked_at' => $this->revoked_at?->toISOString(),
        ];
    }
}
