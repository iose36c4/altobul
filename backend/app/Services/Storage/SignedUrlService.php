<?php

namespace App\Services\Storage;

use App\Models\Photo;
use App\Models\PostAttachment;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Support\Facades\Storage;

class SignedUrlService
{
    public const PHOTO_TTL = 900; // 15 minutes

    public const ATTACHMENT_TTL = 3600; // 1 hour

    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function getPhotoUrl(Photo $photo, User $viewer): ?string
    {
        $result = $this->authz->canViewPhoto($viewer, $photo->user, $photo->id);
        if (! $result->allowed) {
            return null;
        }

        if (! $photo->storage_key || $photo->status !== 'ACTIVE') {
            return null;
        }

        return Storage::disk('s3')->temporaryUrl(
            $photo->storage_key,
            now()->addSeconds(self::PHOTO_TTL),
        );
    }

    public function getPostAttachmentUrl(PostAttachment $attachment, User $viewer): ?string
    {
        $post = $attachment->post;
        if (! $post) {
            return null;
        }

        $result = $this->authz->canViewPost($viewer, $post->user, $post->id);
        if (! $result->allowed) {
            return null;
        }

        if (! $attachment->storage_key) {
            return null;
        }

        return Storage::disk('s3')->temporaryUrl(
            $attachment->storage_key,
            now()->addSeconds(self::ATTACHMENT_TTL),
        );
    }
}
