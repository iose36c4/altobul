<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FriendshipRequest;
use App\Services\Authorization\AuthorizationService;

class FriendshipRequestPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function create(User $requester, User $addressee): bool
    {
        return $this->authz->canRequestFriendship($requester, $addressee)->allowed;
    }

    public function view(User $viewer, FriendshipRequest $request): bool
    {
        return $request->requester_id === $viewer->id || $request->addressee_id === $viewer->id;
    }

    public function accept(User $addressee, FriendshipRequest $request): bool
    {
        return $request->addressee_id === $addressee->id && $request->isPending();
    }

    public function reject(User $addressee, FriendshipRequest $request): bool
    {
        return $request->addressee_id === $addressee->id && $request->isPending();
    }

    public function cancel(User $requester, FriendshipRequest $request): bool
    {
        return $request->requester_id === $requester->id && $request->isPending();
    }
}