<?php

namespace App\Http\Resources;

use App\Models\PostAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();

        $url = $this->resource instanceof PostAttachment
            ? $this->resource->getUrl()
            : $this->getUrl();

        if ($viewer instanceof User && $this->resource instanceof PostAttachment) {
            try {
                $url = $this->resource->getSignedUrl($viewer);
            } catch (\Throwable) {
                // Fall back to public URL if signing fails
            }
        }

        return [
            'id' => $this->id,
            'file_url' => $url,
            'url' => $url,
            'type' => $this->type,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
