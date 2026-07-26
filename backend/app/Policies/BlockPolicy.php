<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Block;
use App\Services\Authorization\AuthorizationService;

class BlockPolicy
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function create(User $blocker, User $blocked): bool
    {
        return $this->authz->canBlock($blocker, $blocked)->allowed;
    }

    public function delete(User $blocker, Block $block): bool
    {
        return $block->blocker_id === $blocker->id && $this->authz->canUnblock($blocker, $block->blocked)->allowed;
    }

    public function view(User $viewer, Block $block): bool
    {
        return $block->blocker_id === $viewer->id;
    }
}