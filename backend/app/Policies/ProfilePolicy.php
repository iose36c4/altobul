<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Profile;
use App\Models\ProfileFieldValue;
use App\Services\Authorization\AuthorizationService;
use App\Domain\Authorization\AuthorizationResult;

class ProfilePolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $viewer, User $owner): bool
    {
        return $this->authz->canViewProfile($viewer, $owner)->allowed;
    }

    public function viewField(User $viewer, ProfileFieldValue $value): bool
    {
        return $this->authz->canViewProfileField($viewer, $value->profile->user, $value->field->slug)->allowed;
    }

    public function update(User $user, Profile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}