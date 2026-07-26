<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserMatch;
use App\Services\Authorization\AuthorizationService;
use App\Domain\Authorization\AuthorizationResult;

class UserMatchPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $viewer, UserMatch $match): bool
    {
        return $match->user_a_id === $viewer->id || $match->user_b_id === $viewer->id;
    }

    public function convertToFriendship(User $user, UserMatch $match): bool
    {
        return $this->authz->canConvertMatchToFriendship($user, $match->userA() === $user ? $match->userB : $match->userA)->allowed;
    }

    public function end(User $user, UserMatch $match): bool
    {
        return ($match->user_a_id === $user->id || $match->user_b_id === $user->id) && $match->status === 'ACTIVE';
    }
}