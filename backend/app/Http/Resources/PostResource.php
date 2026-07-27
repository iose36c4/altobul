<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'content' => $this->content_md,
            'visibility' => $this->visibility,
            'requires_verified' => $this->requires_verified,
            'expires_at' => $this->expires_at?->toISOString(),
            'status' => $this->status,
            'attachment' => new PostAttachmentResource($this->whenLoaded('attachment')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
