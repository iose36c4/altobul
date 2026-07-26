<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Toke;
use App\Services\Authorization\AuthorizationService;

class TokePolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function send(User $sender, User $receiver): bool
    {
        return $this->authz->canSendToke($sender, $receiver)->allowed;
    }

    public function cancel(User $user, Toke $toke): bool
    {
        return $user->id === $toke->sender_id && $toke->status === 'ACTIVE';
    }

    public function view(User $viewer, Toke $toke): bool
    {
        return $viewer->id === $toke->sender_id || $viewer->id === $toke->receiver_id;
    }
}