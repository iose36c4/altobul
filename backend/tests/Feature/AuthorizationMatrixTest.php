<?php

namespace Tests\Feature;

use App\Domain\Authorization\AuthorizationReason;
use App\Domain\Authorization\AuthorizationResult;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Toke;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorizationMatrixTest extends TestCase
{
    use RefreshDatabase;

    private AuthorizationService $authz;

    private User $viewer;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        config(['sanctum.guard' => []]);

        $this->authz = app(AuthorizationService::class);

        $adminId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $adminId,
            'email' => 'admin-test@example.com',
            'password_hash' => bcrypt('password'),
            'email_verified_at' => now(),
            'verification_status' => 'not_verified',
            'status' => 'active',
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $zoneId = (string) Str::uuid();
        DB::table('geo_zones')->insert([
            'id' => $zoneId,
            'name' => 'Global Test Zone',
            'description' => 'Global zone for tests',
            'is_active' => true,
            'created_by' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('geo_polygons')->insert([
            'id' => (string) Str::uuid(),
            'zone_id' => $zoneId,
            'name' => 'World Polygon',
            'geom' => DB::raw("ST_MakePolygon(ST_GeomFromText('LINESTRING(-179 -89, 179 -89, 179 89, -179 89, -179 -89)', 4326))::geography"),
            'created_at' => now(),
        ]);

        $this->owner = $this->createActiveUser('owner@example.com');
        $this->viewer = $this->createActiveUser('viewer@example.com');
    }

    private function createActiveUser(string $email): User
    {
        $id = (string) Str::uuid();

        DB::table('users')->insert([
            'id' => $id,
            'email' => $email,
            'password_hash' => bcrypt('password'),
            'email_verified_at' => now(),
            'verification_status' => 'not_verified',
            'status' => 'active',
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('profiles')->insert([
            'user_id' => $id,
            'location' => DB::raw('ST_SetSRID(ST_MakePoint(0, 0), 4326)'),
            'location_precision_meters' => 1000,
            'discoverable' => true,
            'profile_visibility' => 'PUBLIC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($id);
    }

    private function makeMatch(User $a, User $b): void
    {
        $aId = min($a->id, $b->id);
        $bId = max($a->id, $b->id);

        DB::table('matches')->insert([
            'id' => (string) Str::uuid(),
            'user_a_id' => $aId,
            'user_b_id' => $bId,
            'status' => 'ACTIVE',
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
        ]);
    }

    private function makeFriendship(User $a, User $b): void
    {
        $aId = min($a->id, $b->id);
        $bId = max($a->id, $b->id);

        DB::table('friendships')->insert([
            'id' => (string) Str::uuid(),
            'user_a_id' => $aId,
            'user_b_id' => $bId,
            'status' => 'ACTIVE',
            'created_at' => now(),
        ]);
    }

    private function makeBlock(User $blocker, User $blocked): void
    {
        DB::table('blocks')->insert([
            'id' => (string) Str::uuid(),
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
            'created_at' => now(),
        ]);
    }

    private function makeExpiredPost(User $owner): Post
    {
        return Post::create([
            'user_id' => $owner->id,
            'content_md' => 'Expired post',
            'content_html' => '<p>Expired post</p>',
            'visibility' => 'PUBLIC',
            'status' => 'ACTIVE',
            'expires_at' => '2020-01-01 00:00:00',
        ]);
    }

    private function makeActivePost(User $owner, string $visibility = 'PUBLIC', bool $requiresVerified = false): Post
    {
        return Post::create([
            'user_id' => $owner->id,
            'content_md' => 'Active post',
            'content_html' => '<p>Active post</p>',
            'visibility' => $visibility,
            'requires_verified' => $requiresVerified,
            'status' => 'ACTIVE',
            'expires_at' => now()->addHours(24),
        ]);
    }

    private function makeActivePhoto(User $owner, string $visibility = 'PUBLIC', bool $requiresVerified = false): Photo
    {
        return Photo::create([
            'user_id' => $owner->id,
            'storage_key' => "users/{$owner->id}/".(string) Str::uuid().'.webp',
            'mime_type' => 'image/webp',
            'width' => 800,
            'height' => 600,
            'size_bytes' => 50000,
            'visibility' => $visibility,
            'requires_verified' => $requiresVerified,
            'status' => 'ACTIVE',
        ]);
    }

    private function assertAllowed(AuthorizationResult $result, string $message = ''): void
    {
        $this->assertTrue($result->allowed, $message ?: 'Expected authorization to be allowed');
    }

    private function assertDenied(AuthorizationResult $result, AuthorizationReason $reason, string $message = ''): void
    {
        $this->assertFalse($result->allowed, $message ?: 'Expected authorization to be denied');
        $this->assertEquals($reason, $result->reason, $message ?: "Expected denial reason {$reason->value}");
    }

    // ==========================================
    // PROFILE VISIBILITY MATRIX
    // ==========================================

    public function test_profile_self_view_always_allowed(): void
    {
        $result = $this->authz->canViewProfile($this->owner, $this->owner);
        $this->assertAllowed($result, 'Self-view should always be allowed');
    }

    public function test_profile_public_unrelated_allowed(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'PUBLIC']);
        $this->owner->refresh();

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertAllowed($result);
    }

    public function test_profile_match_visibility_unrelated_denied(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'MATCH']);
        $this->owner->refresh();

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
    }

    public function test_profile_match_visibility_with_match_allowed(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'MATCH']);
        $this->owner->refresh();
        $this->makeMatch($this->viewer, $this->owner);

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertAllowed($result);
    }

    public function test_profile_friends_visibility_unrelated_denied(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'FRIENDS']);
        $this->owner->refresh();

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
    }

    public function test_profile_friends_visibility_with_match_denied(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'FRIENDS']);
        $this->owner->refresh();
        $this->makeMatch($this->viewer, $this->owner);

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
    }

    public function test_profile_friends_visibility_with_friendship_allowed(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'FRIENDS']);
        $this->owner->refresh();
        $this->makeFriendship($this->viewer, $this->owner);

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertAllowed($result);
    }

    public function test_profile_private_unrelated_denied(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'PRIVATE']);
        $this->owner->refresh();

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::NO_EXPLICIT_GRANT);
    }

    public function test_profile_private_with_friendship_denied(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'PRIVATE']);
        $this->owner->refresh();
        $this->makeFriendship($this->viewer, $this->owner);

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::NO_EXPLICIT_GRANT);
    }

    public function test_profile_blocked_always_denied(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'PUBLIC']);
        $this->owner->refresh();
        $this->makeBlock($this->owner, $this->viewer);

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::BLOCKED);
    }

    public function test_profile_blocked_reverse_always_denied(): void
    {
        $this->owner->profile->update(['profile_visibility' => 'PUBLIC']);
        $this->owner->refresh();
        $this->makeBlock($this->viewer, $this->owner);

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::BLOCKED);
    }

    public function test_profile_requires_verified_unverified_denied(): void
    {
        $this->owner->profile->update([
            'profile_visibility' => 'PUBLIC',
            'profile_requires_verified' => true,
        ]);
        $this->owner->refresh();

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::VERIFICATION_REQUIRED);
    }

    public function test_profile_requires_verified_verified_allowed(): void
    {
        $this->owner->profile->update([
            'profile_visibility' => 'PUBLIC',
            'profile_requires_verified' => true,
        ]);
        $this->owner->refresh();

        $this->viewer->update(['verification_status' => 'verified']);
        $this->viewer->refresh();

        $result = $this->authz->canViewProfile($this->viewer, $this->owner);
        $this->assertAllowed($result);
    }

    // ==========================================
    // POST VISIBILITY MATRIX
    // ==========================================

    public function test_post_self_view_always_allowed(): void
    {
        $post = $this->makeActivePost($this->owner);
        $result = $this->authz->canViewPost($this->owner, $this->owner, $post->id);
        $this->assertAllowed($result);
    }

    public function test_post_public_unrelated_allowed(): void
    {
        $post = $this->makeActivePost($this->owner, 'PUBLIC');
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertAllowed($result);
    }

    public function test_post_match_visibility_unrelated_denied(): void
    {
        $post = $this->makeActivePost($this->owner, 'MATCH');
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertDenied($result, AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
    }

    public function test_post_match_visibility_with_match_allowed(): void
    {
        $this->makeMatch($this->viewer, $this->owner);
        $post = $this->makeActivePost($this->owner, 'MATCH');
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertAllowed($result);
    }

    public function test_post_friends_visibility_with_friendship_allowed(): void
    {
        $this->makeFriendship($this->viewer, $this->owner);
        $post = $this->makeActivePost($this->owner, 'FRIENDS');
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertAllowed($result);
    }

    public function test_post_friends_visibility_with_match_denied(): void
    {
        $this->makeMatch($this->viewer, $this->owner);
        $post = $this->makeActivePost($this->owner, 'FRIENDS');
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertDenied($result, AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
    }

    public function test_post_private_unrelated_denied(): void
    {
        $post = $this->makeActivePost($this->owner, 'PRIVATE');
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertDenied($result, AuthorizationReason::NO_EXPLICIT_GRANT);
    }

    public function test_post_expired_denied(): void
    {
        $post = $this->makeExpiredPost($this->owner);
        $result = $this->authz->canViewPost($this->owner, $this->owner, $post->id);
        $this->assertDenied($result, AuthorizationReason::RESOURCE_EXPIRED);
    }

    public function test_post_expired_unrelated_denied(): void
    {
        $post = $this->makeExpiredPost($this->owner);
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertDenied($result, AuthorizationReason::RESOURCE_EXPIRED);
    }

    public function test_post_blocked_denied(): void
    {
        $this->makeBlock($this->owner, $this->viewer);
        $post = $this->makeActivePost($this->owner, 'PUBLIC');
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertDenied($result, AuthorizationReason::BLOCKED);
    }

    public function test_post_requires_verified_unverified_denied(): void
    {
        $post = $this->makeActivePost($this->owner, 'PUBLIC', true);
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertDenied($result, AuthorizationReason::VERIFICATION_REQUIRED);
    }

    public function test_post_requires_verified_verified_allowed(): void
    {
        $this->viewer->update(['verification_status' => 'verified']);
        $this->viewer->refresh();

        $post = $this->makeActivePost($this->owner, 'PUBLIC', true);
        $result = $this->authz->canViewPost($this->viewer, $this->owner, $post->id);
        $this->assertAllowed($result);
    }

    // ==========================================
    // PHOTO VISIBILITY MATRIX
    // ==========================================

    public function test_photo_self_view_always_allowed(): void
    {
        $photo = $this->makeActivePhoto($this->owner);
        $result = $this->authz->canViewPhoto($this->owner, $this->owner, $photo->id);
        $this->assertAllowed($result);
    }

    public function test_photo_public_unrelated_allowed(): void
    {
        $photo = $this->makeActivePhoto($this->owner, 'PUBLIC');
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertAllowed($result);
    }

    public function test_photo_match_visibility_unrelated_denied(): void
    {
        $photo = $this->makeActivePhoto($this->owner, 'MATCH');
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertDenied($result, AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
    }

    public function test_photo_match_visibility_with_match_allowed(): void
    {
        $this->makeMatch($this->viewer, $this->owner);
        $photo = $this->makeActivePhoto($this->owner, 'MATCH');
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertAllowed($result);
    }

    public function test_photo_friends_visibility_with_friendship_allowed(): void
    {
        $this->makeFriendship($this->viewer, $this->owner);
        $photo = $this->makeActivePhoto($this->owner, 'FRIENDS');
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertAllowed($result);
    }

    public function test_photo_friends_visibility_with_match_denied(): void
    {
        $this->makeMatch($this->viewer, $this->owner);
        $photo = $this->makeActivePhoto($this->owner, 'FRIENDS');
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertDenied($result, AuthorizationReason::INSUFFICIENT_RELATIONSHIP);
    }

    public function test_photo_private_unrelated_denied(): void
    {
        $photo = $this->makeActivePhoto($this->owner, 'PRIVATE');
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertDenied($result, AuthorizationReason::NO_EXPLICIT_GRANT);
    }

    public function test_photo_blocked_denied(): void
    {
        $this->makeBlock($this->owner, $this->viewer);
        $photo = $this->makeActivePhoto($this->owner, 'PUBLIC');
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertDenied($result, AuthorizationReason::BLOCKED);
    }

    public function test_photo_requires_verified_unverified_denied(): void
    {
        $photo = $this->makeActivePhoto($this->owner, 'PUBLIC', true);
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertDenied($result, AuthorizationReason::VERIFICATION_REQUIRED);
    }

    public function test_photo_requires_verified_verified_allowed(): void
    {
        $this->viewer->update(['verification_status' => 'verified']);
        $this->viewer->refresh();

        $photo = $this->makeActivePhoto($this->owner, 'PUBLIC', true);
        $result = $this->authz->canViewPhoto($this->viewer, $this->owner, $photo->id);
        $this->assertAllowed($result);
    }

    // ==========================================
    // CONVERSATION MATRIX
    // ==========================================

    public function test_conversation_friends_can_start(): void
    {
        $this->makeFriendship($this->viewer, $this->owner);
        $result = $this->authz->canStartConversation($this->viewer, $this->owner);
        $this->assertAllowed($result);
    }

    public function test_conversation_unrelated_cannot_start(): void
    {
        $result = $this->authz->canStartConversation($this->viewer, $this->owner);
        $this->assertFalse($result->allowed);
    }

    public function test_conversation_blocked_cannot_start(): void
    {
        $this->makeFriendship($this->viewer, $this->owner);
        $this->makeBlock($this->owner, $this->viewer);
        $result = $this->authz->canStartConversation($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::BLOCKED);
    }

    // ==========================================
    // TOKE MATRIX
    // ==========================================

    public function test_toke_unrelated_can_send(): void
    {
        $result = $this->authz->canSendToke($this->viewer, $this->owner);
        $this->assertAllowed($result);
    }

    public function test_toke_self_cannot_send(): void
    {
        $result = $this->authz->canSendToke($this->owner, $this->owner);
        $this->assertDenied($result, AuthorizationReason::SELF_ACTION_FORBIDDEN);
    }

    public function test_toke_blocked_cannot_send(): void
    {
        $this->makeBlock($this->owner, $this->viewer);
        $result = $this->authz->canSendToke($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::BLOCKED);
    }

    public function test_toke_blocked_reverse_cannot_send(): void
    {
        $this->makeBlock($this->viewer, $this->owner);
        $result = $this->authz->canSendToke($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::BLOCKED);
    }

    // ==========================================
    // BLOCK MATRIX
    // ==========================================

    public function test_block_can_block_unrelated(): void
    {
        $result = $this->authz->canBlock($this->viewer, $this->owner);
        $this->assertAllowed($result);
    }

    public function test_block_cannot_block_self(): void
    {
        $result = $this->authz->canBlock($this->owner, $this->owner);
        $this->assertDenied($result, AuthorizationReason::SELF_ACTION_FORBIDDEN);
    }

    public function test_block_cannot_block_twice(): void
    {
        $this->makeBlock($this->viewer, $this->owner);
        $result = $this->authz->canBlock($this->viewer, $this->owner);
        $this->assertDenied($result, AuthorizationReason::INVALID_STATE_TRANSITION);
    }
}
