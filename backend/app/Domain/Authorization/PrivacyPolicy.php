<?php

namespace App\Domain\Authorization;

use App\Domain\Relationship\RelationshipStatus;
use App\Models\User;

readonly class PrivacyPolicy
{
    public function __construct(
        public VisibilityLevel $visibility,
        public bool $requiresVerified = false,
    ) {}

    public function evaluate(
        User $viewer,
        User $owner,
        RelationshipStatus $relationship,
        bool $hasExplicitGrant = false,
        bool $isBlocked = false,
    ): AuthorizationResult {
        // 1. BLOCK OVERRIDE
        if ($isBlocked) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        // 2. USER STATUS
        if ($viewer->status !== 'active' || $owner->status !== 'active') {
            return AuthorizationResult::denied(AuthorizationReason::INACTIVE_USER);
        }

        // 3. RELATIONSHIP vs VISIBILITY
        if (! $this->visibility->satisfies($relationship->level)) {
            return AuthorizationResult::denied(AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
        }

        // 4. VERIFICATION REQUIREMENT
        if ($this->requiresVerified && $viewer->verification_status !== 'verified') {
            return AuthorizationResult::denied(AuthorizationReason::VERIFICATION_REQUIRED);
        }

        // 5. EXPLICIT GRANT (only for PRIVATE)
        if ($this->visibility === VisibilityLevel::PRIVATE && ! $hasExplicitGrant) {
            return AuthorizationResult::denied(AuthorizationReason::NO_EXPLICIT_GRANT);
        }

        return AuthorizationResult::allowed(AuthorizationReason::ALLOWED);
    }
}
