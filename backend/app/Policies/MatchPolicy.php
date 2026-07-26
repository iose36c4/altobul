<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Match;
use App\Services\Authorization\AuthorizationService;

class MatchPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $viewer, Match $match): bool
    {
        return $match->user_a_id === $viewer->id || $match->user_b_id === $viewer->id;
    }

    public function convertToFriendship(User $user, Match $match): bool
    {
        return $this->authz->canConvertMatchToFriendship($user, $match->userA() === $user ? $match->userB : $match->userA)->allowed;
    }

    public function end(User $user, Match $match): bool
    {
        return ($match->user_a_id === $user->id || $match->user_b_id === $user->id) && $match->status === 'ACTIVE';
    }
}