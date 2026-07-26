<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use App\Domain\Authorization\AuthorizationResult;

class UserPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $viewer, User $owner): bool
    {
        return $this->authz->canViewProfile($viewer, $owner)->allowed;
    }

    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }
}