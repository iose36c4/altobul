<?php

namespace App\Http\Resources;

use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PostAttachment $attachment */
        $attachment = $this->resource;
        
        return [
            'url' => $attachment->getUrl(),
            'width' => $attachment->width,
            'height' => $attachment->height,
        ];
    }
}