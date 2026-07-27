<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;

class PhotoPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $viewer, Photo $photo): bool
    {
        return $this->authz->canViewPhoto($viewer, $photo->user, $photo->id)->allowed;
    }

    public function manage(User $user, Photo $photo): bool
    {
        return $user->id === $photo->user_id;
    }

    public function grantAccess(User $owner, User $grantee, Photo $photo): bool
    {
        return $this->authz->canGrantAccess($owner, $grantee, 'photo', $photo->id)->allowed;
    }

    public function revokeAccess(User $owner, User $grantee, Photo $photo): bool
    {
        return $this->authz->canRevokeGrant($owner, $grantee, 'photo', $photo->id)->allowed;
    }
}
