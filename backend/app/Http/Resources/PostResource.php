<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Post $post */
        $post = $this->resource;

        return [
            'id' => $post->id,
            'content_md' => $post->content_md,
            'content_html' => $post->content_html,
            'visibility' => $post->visibility,
            'requires_verified' => $post->requires_verified,
            'expires_at' => $post->expires_at?->toISOString(),
            'is_expired' => $post->is_expired,
            'attachment' => $post->attachment ? new PostAttachmentResource($post->attachment) : null,
            'created_at' => $post->created_at?->toISOString(),
            'updated_at' => $post->updated_at?->toISOString(),
        ];
    }
}
