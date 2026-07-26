<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Authorization\AuthorizationService;

class ConversationPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->user_a_id === $user->id || $conversation->user_b_id === $user->id;
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->authz->canSendMessage($user, $conversation->getOtherUser($user->id))->allowed;
    }

    public function end(User $user, Conversation $conversation): bool
    {
        return ($conversation->user_a_id === $user->id || $conversation->user_b_id === $user->id) 
            && $conversation->status === 'ACTIVE';
    }
}