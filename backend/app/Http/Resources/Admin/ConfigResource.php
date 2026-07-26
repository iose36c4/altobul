<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'description' => $this->description,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}