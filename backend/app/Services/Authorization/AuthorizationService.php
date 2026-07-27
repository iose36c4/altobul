<?php

namespace App\Services\Authorization;

use App\Contracts\AuthorizationServiceInterface;
use App\Domain\Authorization\AuthorizationReason;
use App\Domain\Authorization\AuthorizationResult;
use App\Domain\Authorization\VisibilityLevel;
use App\Domain\Relationship\RelationshipLevel;
use App\Domain\Relationship\RelationshipStatus;
use App\Models\Block;
use App\Models\Conversation;
use App\Models\Friendship;
use App\Models\FriendshipRequest;
use App\Models\Photo;
use App\Models\PhotoAccess;
use App\Models\Post;
use App\Models\PostAccess;
use App\Models\ProfileFieldValue;
use App\Models\ProfileFieldValueAccess;
use App\Models\Toke;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\Config\ConfigService;
use App\Services\Geo\GeoZoneService;
use Illuminate\Support\Facades\Log;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private ConfigService $configService,
        private GeoZoneService $geoZoneService,
    ) {}

    public function canDiscover(User $viewer, User $target): AuthorizationResult
    {
        // 1. Block check (bidirectional)
        if ($this->isBlocked($viewer, $target)) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        // 2. User status
        if ($target->status !== 'active' || $viewer->status !== 'active') {
            return AuthorizationResult::denied(AuthorizationReason::INACTIVE_USER);
        }

        // 3. Discoverable setting
        if (! $target->profile?->discoverable) {
            return AuthorizationResult::denied(AuthorizationReason::NOT_DISCOVERABLE);
        }

        // 4. Geo zone
        if (! $this->geoZoneService->isInActiveZone($target->profile)) {
            return AuthorizationResult::denied(AuthorizationReason::NOT_IN_ACTIVE_ZONE);
        }

        // 5. Profile visibility
        $profileVis = $target->profile?->profile_visibility ?? VisibilityLevel::PUBLIC;
        $requiresVerified = $target->profile?->profile_requires_verified ?? false;
        $relationship = $this->getRelationshipStatus($viewer, $target);

        if (! $profileVis->satisfies($relationship->level)) {
            return AuthorizationResult::denied(AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
        }

        if ($requiresVerified && ! $viewer->isVerified()) {
            return AuthorizationResult::denied(AuthorizationReason::VERIFICATION_REQUIRED);
        }

        return AuthorizationResult::allowed();
    }

    public function canViewProfile(User $viewer, User $owner): AuthorizationResult
    {
        $visibility = $owner->profile?->profile_visibility
            ? VisibilityLevel::tryFrom($owner->profile->profile_visibility)
            : VisibilityLevel::PUBLIC;
        $requiresVerified = $owner->profile?->profile_requires_verified ?? false;

        return $this->evaluateResourceAccess(
            $viewer, $owner,
            $visibility,
            $requiresVerified,
            'profile', $owner->id
        );
    }

    public function canViewProfileField(User $viewer, User $owner, string $fieldSlug): AuthorizationResult
    {
        $fieldValue = ProfileFieldValue::whereHas('field', fn ($q) => $q->where('slug', $fieldSlug))
            ->where('profile_id', $owner->id)
            ->first();

        if (! $fieldValue) {
            return AuthorizationResult::denied(AuthorizationReason::RESOURCE_DELETED);
        }

        $visibility = $fieldValue->visibility_override ?? $fieldValue->field->default_visibility;
        $requiresVerified = $fieldValue->requires_verified_override ?? $fieldValue->field->default_requires_verified;

        return $this->evaluateResourceAccess(
            $viewer, $owner,
            is_string($visibility) ? (VisibilityLevel::tryFrom($visibility) ?? VisibilityLevel::PUBLIC) : $visibility,
            $requiresVerified,
            'profile_field', $fieldValue->id
        );
    }

    public function canViewProfileFixedField(User $viewer, User $owner, string $fieldName, string $visibility, bool $requiresVerified): AuthorizationResult
    {
        return $this->evaluateResourceAccess(
            $viewer, $owner,
            VisibilityLevel::tryFrom($visibility) ?? VisibilityLevel::PUBLIC,
            $requiresVerified,
            'profile_fixed_field', $fieldName
        );
    }

    public function canViewPhoto(User $viewer, User $owner, string $photoId): AuthorizationResult
    {
        $photo = Photo::find($photoId);
        if (! $photo) {
            return AuthorizationResult::denied(AuthorizationReason::RESOURCE_DELETED);
        }

        if ($photo->isExpired()) {
            return AuthorizationResult::denied(AuthorizationReason::RESOURCE_EXPIRED);
        }

        return $this->evaluateResourceAccess(
            $viewer, $owner,
            is_string($photo->visibility) ? (VisibilityLevel::tryFrom($photo->visibility) ?? VisibilityLevel::PUBLIC) : $photo->visibility,
            $photo->requires_verified,
            'photo', $photoId
        );
    }

    public function canViewPost(User $viewer, User $owner, string $postId): AuthorizationResult
    {
        $post = Post::find($postId);
        if (! $post) {
            return AuthorizationResult::denied(AuthorizationReason::RESOURCE_DELETED);
        }

        if ($post->isExpired()) {
            return AuthorizationResult::denied(AuthorizationReason::RESOURCE_EXPIRED);
        }

        return $this->evaluateResourceAccess(
            $viewer, $owner,
            is_string($post->visibility) ? (VisibilityLevel::tryFrom($post->visibility) ?? VisibilityLevel::PUBLIC) : $post->visibility,
            $post->requires_verified,
            'post', $postId
        );
    }

    public function canSendToke(User $sender, User $receiver): AuthorizationResult
    {
        if ($sender->id === $receiver->id) {
            return AuthorizationResult::denied(AuthorizationReason::SELF_ACTION_FORBIDDEN);
        }

        if ($this->isBlocked($sender, $receiver)) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        if ($receiver->status !== 'active') {
            return AuthorizationResult::denied(AuthorizationReason::INACTIVE_USER);
        }

        // Check if receiver is in active zone (optional)
        $inZone = $this->geoZoneService->isInActiveZone($receiver->profile);
        Log::debug('GeoZone check', [
            'receiver_id' => $receiver->id,
            'has_profile' => $receiver->profile !== null,
            'has_location' => $receiver->profile?->location !== null,
            'location' => $receiver->profile?->location,
            'inZone' => $inZone,
        ]);

        if (! $inZone) {
            return AuthorizationResult::denied(AuthorizationReason::NOT_IN_ACTIVE_ZONE);
        }

        // Check for existing active toke
        $existing = Toke::where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->whereIn('status', ['ACTIVE', 'CONSUMED'])
            ->exists();

        if ($existing) {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        return AuthorizationResult::allowed();
    }

    public function canConvertMatchToFriendship(User $initiator, User $target): AuthorizationResult
    {
        if ($this->isBlocked($initiator, $target)) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        $match = UserMatch::between($initiator, $target)->active()->first();
        if (! $match) {
            return AuthorizationResult::denied(AuthorizationReason::MATCH_EXPIRED);
        }

        // Check if friendship already exists
        $friendship = Friendship::between($initiator, $target)->active()->first();
        if ($friendship) {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        return AuthorizationResult::allowed();
    }

    public function canRequestFriendship(User $requester, User $addressee): AuthorizationResult
    {
        if ($requester->id === $addressee->id) {
            return AuthorizationResult::denied(AuthorizationReason::SELF_ACTION_FORBIDDEN);
        }

        if ($this->isBlocked($requester, $addressee)) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        if ($addressee->status !== 'active') {
            return AuthorizationResult::denied(AuthorizationReason::INACTIVE_USER);
        }

        // Check existing pending request
        $existing = FriendshipRequest::where(function ($q) use ($requester, $addressee) {
            $q->where('requester_id', $requester->id)
                ->where('addressee_id', $addressee->id);
        })->orWhere(function ($q) use ($requester, $addressee) {
            $q->where('requester_id', $addressee->id)
                ->where('addressee_id', $requester->id);
        })->where('status', 'PENDING')->exists();

        if ($existing) {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        // Check existing friendship
        $friendship = Friendship::between($requester, $addressee)->active()->first();
        if ($friendship) {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        return AuthorizationResult::allowed();
    }

    public function canAcceptFriendshipRequest(User $addressee, User $requester): AuthorizationResult
    {
        if ($this->isBlocked($addressee, $requester)) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        return AuthorizationResult::allowed();
    }

    public function canEndFriendship(User $initiator, User $target): AuthorizationResult
    {
        $friendship = Friendship::between($initiator, $target)->active()->first();
        if (! $friendship) {
            return AuthorizationResult::denied(AuthorizationReason::FRIENDSHIP_ENDED);
        }

        return AuthorizationResult::allowed();
    }

    public function canBlock(User $blocker, User $blocked): AuthorizationResult
    {
        if ($blocker->id === $blocked->id) {
            return AuthorizationResult::denied(AuthorizationReason::SELF_ACTION_FORBIDDEN);
        }

        if (Block::where('blocker_id', $blocker->id)
            ->where('blocked_id', $blocked->id)
            ->exists()) {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        return AuthorizationResult::allowed();
    }

    public function canUnblock(User $blocker, User $blocked): AuthorizationResult
    {
        if (! Block::where('blocker_id', $blocker->id)
            ->where('blocked_id', $blocked->id)
            ->exists()) {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        return AuthorizationResult::allowed();
    }

    public function isBlocked(User $a, User $b): bool
    {
        return Block::where(function ($q) use ($a, $b) {
            $q->where('blocker_id', $a->id)->where('blocked_id', $b->id);
        })->orWhere(function ($q) use ($a, $b) {
            $q->where('blocker_id', $b->id)->where('blocked_id', $a->id);
        })->exists();
    }

    public function canChat(User $a, User $b): AuthorizationResult
    {
        $result = $this->canStartConversation($a, $b);
        if (! $result->allowed) {
            return $result;
        }

        $conv = Conversation::between($a, $b)->first();
        if (! $conv || ! $conv->isActive()) {
            return AuthorizationResult::denied(AuthorizationReason::CONVERSATION_ENDED);
        }

        return AuthorizationResult::allowed();
    }

    public function canStartConversation(User $a, User $b): AuthorizationResult
    {
        if ($this->isBlocked($a, $b)) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        $relationship = $this->getRelationshipStatus($a, $b);
        if (! $relationship->canChat()) {
            if ($relationship->level === RelationshipLevel::MATCH) {
                return AuthorizationResult::denied(AuthorizationReason::MATCH_EXPIRED);
            }

            return AuthorizationResult::denied(AuthorizationReason::FRIENDSHIP_ENDED);
        }

        return AuthorizationResult::allowed();
    }

    public function canSendMessage(User $sender, Conversation $conversation): AuthorizationResult
    {
        return $this->canAccessConversation($sender, $conversation);
    }

    public function canViewConversation(User $viewer, Conversation $conversation): AuthorizationResult
    {
        return $this->canAccessConversation($viewer, $conversation);
    }

    public function canAccessConversation(User $user, Conversation $conversation): AuthorizationResult
    {
        if (! $conversation->hasParticipant($user)) {
            return AuthorizationResult::denied(AuthorizationReason::UNAUTHORIZED);
        }

        if (! $conversation->isActive()) {
            return AuthorizationResult::denied(AuthorizationReason::CONVERSATION_ENDED);
        }

        $other = $conversation->getOtherUser($user);
        if (! $other) {
            return AuthorizationResult::denied(AuthorizationReason::UNAUTHORIZED);
        }

        return $this->canStartConversation($user, $other);
    }

    public function canGrantAccess(User $owner, User $grantee, string $resourceType, string $resourceId): AuthorizationResult
    {
        if ($owner->id === $grantee->id) {
            return AuthorizationResult::denied(AuthorizationReason::SELF_ACTION_FORBIDDEN);
        }

        if ($this->isBlocked($owner, $grantee)) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        // Verify resource exists and belongs to owner
        $resource = $this->getResource($resourceType, $resourceId);
        if (! $resource || $resource->user_id !== $owner->id) {
            return AuthorizationResult::denied(AuthorizationReason::RESOURCE_DELETED);
        }

        // Only PRIVATE resources can have grants
        $visibility = $resource->visibility ?? ($resource->default_visibility ?? null);
        if ($visibility !== VisibilityLevel::PRIVATE) {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        return AuthorizationResult::allowed();
    }

    public function canRevokeGrant(User $owner, User $grantee, string $resourceType, string $resourceId): AuthorizationResult
    {
        if ($owner->id === $grantee->id) {
            return AuthorizationResult::denied(AuthorizationReason::SELF_ACTION_FORBIDDEN);
        }

        $grant = $this->getGrant($resourceType, $resourceId, $grantee);
        if (! $grant) {
            return AuthorizationResult::denied(AuthorizationReason::RESOURCE_DELETED);
        }

        return AuthorizationResult::allowed();
    }

    public function canRequestVerification(User $user): AuthorizationResult
    {
        if ($user->verification_status === 'pending') {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        if ($user->verification_status === 'verified') {
            return AuthorizationResult::denied(AuthorizationReason::INVALID_STATE_TRANSITION);
        }

        return AuthorizationResult::allowed();
    }

    public function canReviewVerification(User $admin, string $requestId): AuthorizationResult
    {
        if (! $admin->isAdmin()) {
            return AuthorizationResult::denied(AuthorizationReason::UNAUTHORIZED);
        }

        return AuthorizationResult::allowed();
    }

    public function canAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    public function getRelationshipStatus(User $a, User $b): RelationshipStatus
    {
        // 1. Check Friendship (highest priority)
        $friendship = Friendship::between($a, $b)->active()->first();
        if ($friendship) {
            return RelationshipStatus::friendship($friendship);
        }

        // 2. Check Match
        $match = UserMatch::between($a, $b)->active()->first();
        if ($match) {
            return RelationshipStatus::fromMatch($match);
        }

        // 3. Check Mutual Toke - properly grouped with status filter
        $mutualTokes = Toke::where(function ($q) use ($a, $b) {
            $q->where('sender_id', $a->id)->where('receiver_id', $b->id);
            $q->orWhere(function ($q2) use ($a, $b) {
                $q2->where('sender_id', $b->id)->where('receiver_id', $a->id);
            });
        })->where('status', 'ACTIVE')->where('expires_at', '>', now())->count();

        if ($mutualTokes === 2) {
            return RelationshipStatus::mutualToke();
        }

        // 4. Check Unidirectional Toke
        $toke = Toke::where('sender_id', $a->id)
            ->where('receiver_id', $b->id)
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', now())
            ->first();
        if ($toke) {
            return RelationshipStatus::toked();
        }

        return RelationshipStatus::none();
    }

    public function evaluateResourceAccess(
        User $viewer,
        User $owner,
        VisibilityLevel $visibility,
        bool $requiresVerified,
        string $resourceType,
        string $resourceId
    ): AuthorizationResult {
        // 0. SELF-VIEW: users can always view their own resources
        if ($viewer->id === $owner->id) {
            // Still check expiration for time-limited resources
            if (in_array($resourceType, ['post', 'toke', 'match'])) {
                $model = $this->getResource($resourceType, $resourceId);
                if ($model && $model->isExpired()) {
                    return AuthorizationResult::denied(AuthorizationReason::RESOURCE_EXPIRED);
                }
            }

            return AuthorizationResult::allowed();
        }

        // 1. BLOCK - absolute override
        if ($this->isBlocked($viewer, $owner)) {
            return AuthorizationResult::denied(AuthorizationReason::BLOCKED);
        }

        // 2. USER STATUS
        if ($viewer->status !== 'active' || $owner->status !== 'active') {
            return AuthorizationResult::denied(AuthorizationReason::INACTIVE_USER);
        }

        // 3. EXPIRATION CHECK
        if (in_array($resourceType, ['post', 'toke', 'match'])) {
            $model = $this->getResource($resourceType, $resourceId);
            if ($model && $model->isExpired()) {
                return AuthorizationResult::denied(AuthorizationReason::RESOURCE_EXPIRED);
            }
        }

        // 4. RELATIONSHIP vs VISIBILITY
        $relationship = $this->getRelationshipStatus($viewer, $owner);

        // 5. EXPLICIT GRANT (only for PRIVATE - checked before visibility gate)
        if ($visibility === VisibilityLevel::PRIVATE) {
            $hasGrant = $this->hasActiveGrant($viewer, $resourceType, $resourceId);
            if ($hasGrant) {
                // Grant holder bypasses visibility check
                if ($requiresVerified && ! $viewer->isVerified()) {
                    return AuthorizationResult::denied(AuthorizationReason::VERIFICATION_REQUIRED);
                }

                return AuthorizationResult::allowed();
            }

            return AuthorizationResult::denied(AuthorizationReason::NO_EXPLICIT_GRANT);
        }

        // 6. VISIBILITY GATE (PUBLIC, MATCH, FRIENDS)
        if (! $visibility->satisfies($relationship->level)) {
            return AuthorizationResult::denied(AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
        }

        // 7. VERIFICATION REQUIREMENT
        if ($requiresVerified && ! $viewer->isVerified()) {
            return AuthorizationResult::denied(AuthorizationReason::VERIFICATION_REQUIRED);
        }

        return AuthorizationResult::allowed();
    }

    private function getResource(string $type, string $id)
    {
        return match ($type) {
            'photo' => Photo::find($id),
            'post' => Post::find($id),
            'profile_field' => ProfileFieldValue::find($id),
            'toke' => Toke::find($id),
            'match' => UserMatch::find($id),
            default => null,
        };
    }

    private function hasActiveGrant(User $grantee, string $resourceType, string $resourceId): bool
    {
        return match ($resourceType) {
            'photo' => PhotoAccess::where('photo_id', $resourceId)
                ->where('grantee_id', $grantee->id)
                ->whereNull('revoked_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->exists(),
            'post' => PostAccess::where('post_id', $resourceId)
                ->where('grantee_id', $grantee->id)
                ->whereNull('revoked_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->exists(),
            'profile_field' => ProfileFieldValueAccess::where('field_value_id', $resourceId)
                ->where('grantee_id', $grantee->id)
                ->whereNull('revoked_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->exists(),
            default => false,
        };
    }

    private function getGrant(string $resourceType, string $resourceId, User $grantee)
    {
        return match ($resourceType) {
            'photo' => PhotoAccess::where('photo_id', $resourceId)
                ->where('grantee_id', $grantee->id)
                ->whereNull('revoked_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->first(),
            'post' => PostAccess::where('post_id', $resourceId)
                ->where('grantee_id', $grantee->id)
                ->whereNull('revoked_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->first(),
            'profile_field' => ProfileFieldValueAccess::where('field_value_id', $resourceId)
                ->where('grantee_id', $grantee->id)
                ->whereNull('revoked_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->first(),
            default => null,
        };
    }
}
