<?php

namespace App\Http\Resources\Admin;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiKeyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ApiKey $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'key_prefix' => $this->key_prefix,
            'last_used_at' => $this->last_used_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'revoked_at' => $this->revoked_at?->toISOString(),
            'created_by' => $this->creator ? [
                'id' => $this->creator->id,
                'email' => $this->creator->email,
            ] : null,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'is_valid' => $this->isValid(),
        ];
    }
}
