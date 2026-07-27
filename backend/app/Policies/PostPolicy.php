<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;

class PostPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $viewer, Post $post): bool
    {
        return $this->authz->canViewPost($viewer, $post->user, $post->id)->allowed;
    }

    public function manage(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function grantAccess(User $owner, User $grantee, Post $post): bool
    {
        return $this->authz->canGrantAccess($owner, $grantee, 'post', $post->id)->allowed;
    }

    public function revokeAccess(User $owner, User $grantee, Post $post): bool
    {
        return $this->authz->canRevokeGrant($owner, $grantee, 'post', $post->id)->allowed;
    }
}
