<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Friendship;
use App\Services\Authorization\AuthorizationService;

class FriendshipPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $viewer, Friendship $friendship): bool
    {
        return $friendship->user_a_id === $viewer->id || $friendship->user_b_id === $viewer->id;
    }

    public function end(User $user, Friendship $friendship): bool
    {
        return $this->authz->canEndFriendship($user, $friendship->userA() === $user ? $friendship->userB : $friendship->userA)->allowed;
    }
}