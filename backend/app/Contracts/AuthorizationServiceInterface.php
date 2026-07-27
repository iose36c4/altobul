<?php

namespace App\Contracts;

use App\Domain\Authorization\AuthorizationResult;
use App\Domain\Authorization\VisibilityLevel;
use App\Domain\Relationship\RelationshipStatus;
use App\Models\User;

interface AuthorizationServiceInterface
{
    public function canDiscover(User $viewer, User $target): AuthorizationResult;

    public function canViewProfile(User $viewer, User $owner): AuthorizationResult;

    public function canViewProfileField(User $viewer, User $owner, string $fieldSlug): AuthorizationResult;

    public function canViewProfileFixedField(User $viewer, User $owner, string $fieldName, string $visibility, bool $requiresVerified): AuthorizationResult;

    public function evaluateResourceAccess(
        User $viewer,
        User $owner,
        VisibilityLevel $visibility,
        bool $requiresVerified,
        string $resourceType,
        string $resourceId
    ): AuthorizationResult;

    public function canViewPhoto(User $viewer, User $owner, string $photoId): AuthorizationResult;

    public function canViewPost(User $viewer, User $owner, string $postId): AuthorizationResult;

    public function canSendToke(User $sender, User $receiver): AuthorizationResult;

    public function canConvertMatchToFriendship(User $initiator, User $target): AuthorizationResult;

    public function canRequestFriendship(User $requester, User $addressee): AuthorizationResult;

    public function canAcceptFriendshipRequest(User $addressee, User $requester): AuthorizationResult;

    public function canEndFriendship(User $initiator, User $target): AuthorizationResult;

    public function canBlock(User $blocker, User $blocked): AuthorizationResult;

    public function canUnblock(User $blocker, User $blocked): AuthorizationResult;

    public function isBlocked(User $a, User $b): bool;

    public function canChat(User $a, User $b): AuthorizationResult;

    public function canSendMessage(User $sender, User $receiver): AuthorizationResult;

    public function canGrantAccess(User $owner, User $grantee, string $resourceType, string $resourceId): AuthorizationResult;

    public function canRevokeGrant(User $owner, User $grantee, string $resourceType, string $resourceId): AuthorizationResult;

    public function canRequestVerification(User $user): AuthorizationResult;

    public function canReviewVerification(User $admin, string $requestId): AuthorizationResult;

    public function canAdmin(User $user): bool;

    public function getRelationshipStatus(User $a, User $b): RelationshipStatus;
}
