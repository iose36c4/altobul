<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;

class ConversationPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function view(User $user, Conversation $conversation): bool
    {
        return $this->authz->canViewConversation($user, $conversation)->allowed;
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->authz->canSendMessage($user, $conversation)->allowed;
    }

    public function end(User $user, Conversation $conversation): bool
    {
        return $this->authz->canAccessConversation($user, $conversation)->allowed;
    }
}
