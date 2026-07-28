<?php

namespace App\Policies;

use App\Models\ProfileFieldValue;
use App\Models\ProfileFieldValueAccess;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;

class ProfileFieldValueAccessPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function grant(User $user, ProfileFieldValue $fieldValue): bool
    {
        return $this->authz->canGrantAccess($user, $fieldValue->profile->user, 'profile_field', $fieldValue->id)->allowed;
    }

    public function revoke(User $user, ProfileFieldValueAccess $access): bool
    {
        return $user->id === $access->granted_by;
    }

    public function view(User $user, ProfileFieldValueAccess $access): bool
    {
        $fieldValue = $access->fieldValue;

        return $this->authz->canViewProfileField($user, $fieldValue->profile->user, $fieldValue->field->slug)->allowed;
    }

    public function viewAny(User $user, ProfileFieldValue $fieldValue): bool
    {
        return $this->authz->canViewProfileField($user, $fieldValue->profile->user, $fieldValue->field->slug)->allowed;
    }

    public function manageGrants(User $user, ProfileFieldValue $fieldValue): bool
    {
        return $user->id === $fieldValue->profile->user_id;
    }
}
