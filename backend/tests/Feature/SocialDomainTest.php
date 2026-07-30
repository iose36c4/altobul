<?php

namespace Tests\Feature;

use App\Models\Friendship;
use App\Models\FriendshipRequest;
use App\Models\Photo;
use App\Models\Toke;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SocialDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable session-based guard fallback so Sanctum only uses Bearer tokens
        config(['sanctum.guard' => []]);

        $adminId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $adminId,
            'email' => 'geo-admin@example.com',
            'password_hash' => Hash::make('password'),
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
    }

    protected function createUser(array $attributes = []): User
    {
        $userId = (string) Str::uuid();

        DB::table('users')->insert([
            'id' => $userId,
            'email' => $attributes['email'] ?? 'user-'.Str::uuid().'@example.com',
            'password_hash' => Hash::make('password'),
            'email_verified_at' => now(),
            'verification_status' => 'not_verified',
            'status' => 'active',
            'role' => $attributes['role'] ?? 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('profiles')->insert([
            'user_id' => $userId,
            'location' => DB::raw('ST_SetSRID(ST_MakePoint(0, 0), 4326)'),
            'location_precision_meters' => 1000,
            'discoverable' => true,
            'profile_visibility' => 'PUBLIC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    protected function createAdmin(array $attributes = []): User
    {
        $attributes['role'] = 'admin';

        return $this->createUser($attributes);
    }

    protected function createApiKey(User $creator, string $type): array
    {
        $service = new ApiKeyService;

        return $service->createApiKey($creator, "Test {$type} Key", $type);
    }

    protected function getHeaders(string $apiKey): array
    {
        return ['X-API-Key' => $apiKey];
    }

    protected function apiGet(string $url, string $apiKey, ?string $token = null): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->withHeaders(array_merge(
            $this->getHeaders($apiKey),
            $token ? ['Authorization' => 'Bearer '.$token] : [],
        ))->getJson($url);
    }

    protected function apiPost(string $url, string $apiKey, ?string $token = null, array $data = []): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->withHeaders(array_merge(
            $this->getHeaders($apiKey),
            $token ? ['Authorization' => 'Bearer '.$token] : [],
        ))->postJson($url, $data);
    }

    protected function apiDelete(string $url, string $apiKey, ?string $token = null): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->withHeaders(array_merge(
            $this->getHeaders($apiKey),
            $token ? ['Authorization' => 'Bearer '.$token] : [],
        ))->deleteJson($url);
    }

    protected function apiPatch(string $url, string $apiKey, ?string $token = null, array $data = []): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->withHeaders(array_merge(
            $this->getHeaders($apiKey),
            $token ? ['Authorization' => 'Bearer '.$token] : [],
        ))->patchJson($url, $data);
    }

    private function login(string $email, string $rawKey): string
    {
        $response = $this->apiPost('/api/client/auth/login', $rawKey, null, [
            'email' => $email,
            'password' => 'password',
        ]);
        $this->assertEquals(200, $response->status());

        return $response->json('token');
    }

    public function test_toke_flow_complete_lifecycle(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');

        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);

        $tokenA = $this->login($userA->email, $clientKey['raw_key']);
        $tokenB = $this->login($userB->email, $clientKey['raw_key']);

        // User A sends toke to User B
        $response = $this->apiPost('/api/client/tokes', $clientKey['raw_key'], $tokenA, ['receiver_id' => $userB->id]);

        $this->assertEquals(201, $response->status());
        $tokeId = $response->json('toke.id');

        // Verify toke exists
        $this->assertDatabaseHas('tokes', [
            'id' => $tokeId,
            'sender_id' => $userA->id,
            'receiver_id' => $userB->id,
            'status' => 'ACTIVE',
        ]);

        // User B can see received tokes
        $response = $this->apiGet('/api/client/tokes', $clientKey['raw_key'], $tokenB);

        $this->assertEquals(200, $response->status());
        $this->assertEquals($tokeId, $response->json('received.data.0.id'));

        // User B consumes toke (creates match)
        $response = $this->apiPost("/api/client/tokes/{$tokeId}/consume", $clientKey['raw_key'], $tokenB);

        $this->assertEquals(200, $response->status());
        $this->assertTrue($response->json('match_created'));

        // Verify match was created
        $match = UserMatch::between($userA, $userB)->active()->first();
        $this->assertNotNull($match);

        // Toke should be consumed
        $this->assertDatabaseHas('tokes', [
            'id' => $tokeId,
            'status' => 'CONSUMED',
        ]);
    }

    public function test_match_to_friendship_conversion(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');

        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);

        $tokenA = $this->login($userA->email, $clientKey['raw_key']);
        $tokenB = $this->login($userB->email, $clientKey['raw_key']);

        // A -> B
        $this->apiPost('/api/client/tokes', $clientKey['raw_key'], $tokenA, ['receiver_id' => $userB->id]);

        // B -> A (mutual)
        $this->apiPost('/api/client/tokes', $clientKey['raw_key'], $tokenB, ['receiver_id' => $userA->id]);

        // Consume to create match
        $tokes = Toke::where('sender_id', $userB->id)
            ->where('receiver_id', $userA->id)
            ->where('status', 'ACTIVE')
            ->get();
        $toke = $tokes->first();

        $this->apiPost("/api/client/tokes/{$toke->id}/consume", $clientKey['raw_key'], $tokenA);

        // Find match
        $match = UserMatch::between($userA, $userB)->active()->first();
        $this->assertNotNull($match);

        // Convert match to friendship
        $response = $this->apiPost("/api/client/matches/{$match->id}/convert-to-friendship", $clientKey['raw_key'], $tokenA);

        $this->assertEquals(200, $response->status());
        $this->assertNotNull($response->json('friendship.id'));

        // Match should be ended
        $match->refresh();
        $this->assertEquals('ENDED', $match->status);

        // Friendship should exist
        $friendship = Friendship::between($userA, $userB)->active()->first();
        $this->assertNotNull($friendship);
    }

    public function test_friendship_request_flow(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');

        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);

        $tokenA = $this->login($userA->email, $clientKey['raw_key']);
        $tokenB = $this->login($userB->email, $clientKey['raw_key']);

        // User A sends friendship request to User B
        $response = $this->apiPost('/api/client/friendships', $clientKey['raw_key'], $tokenA, ['addressee_id' => $userB->id]);

        $this->assertEquals(201, $response->status());
        $requestId = $response->json('friendship_request.id');

        // User B can see received request
        $response = $this->apiGet('/api/client/friendship-requests', $clientKey['raw_key'], $tokenB);

        $this->assertEquals(200, $response->status());
        $this->assertEquals($requestId, $response->json('received.data.0.id'));

        // User B accepts
        $response = $this->apiPost("/api/client/friendship-requests/{$requestId}/accept", $clientKey['raw_key'], $tokenB);

        $this->assertEquals(200, $response->status());
        $this->assertNotNull($response->json('friendship.id'));

        // Friendship should be active
        $friendship = Friendship::between($userA, $userB)->active()->first();
        $this->assertNotNull($friendship);

        // Request should be accepted
        $request = FriendshipRequest::find($requestId);
        $this->assertEquals('ACCEPTED', $request->status);
    }

    public function test_block_prevents_all_interactions(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');

        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);

        $tokenA = $this->login($userA->email, $clientKey['raw_key']);
        $tokenB = $this->login($userB->email, $clientKey['raw_key']);

        // User A blocks User B
        $response = $this->apiPost('/api/client/blocks', $clientKey['raw_key'], $tokenA, ['blocked_id' => $userB->id]);

        $this->assertEquals(201, $response->status());

        // User A should not be able to send toke to User B
        $response = $this->apiPost('/api/client/tokes', $clientKey['raw_key'], $tokenA, ['receiver_id' => $userB->id]);

        $this->assertEquals(403, $response->status());

        // User B should not be able to send toke to User A
        $response = $this->apiPost('/api/client/tokes', $clientKey['raw_key'], $tokenB, ['receiver_id' => $userA->id]);

        $this->assertEquals(403, $response->status());

        // User B should not be able to send friendship request
        $response = $this->apiPost('/api/client/friendships', $clientKey['raw_key'], $tokenB, ['addressee_id' => $userA->id]);

        $this->assertEquals(403, $response->status());
    }

    public function test_conversation_and_messages(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');

        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);

        $tokenA = $this->login($userA->email, $clientKey['raw_key']);
        $tokenB = $this->login($userB->email, $clientKey['raw_key']);

        // First establish a match between users (mutual tokes)
        $this->apiPost('/api/client/tokes', $clientKey['raw_key'], $tokenA, ['receiver_id' => $userB->id]);

        $this->apiPost('/api/client/tokes', $clientKey['raw_key'], $tokenB, ['receiver_id' => $userA->id]);

        // Consume to create match
        $tokes = Toke::where('sender_id', $userB->id)
            ->where('receiver_id', $userA->id)
            ->where('status', 'ACTIVE')
            ->get();
        $toke = $tokes->first();

        $this->apiPost("/api/client/tokes/{$toke->id}/consume", $clientKey['raw_key'], $tokenA);

        // Now create conversation
        $response = $this->apiPost('/api/client/conversations', $clientKey['raw_key'], $tokenA, ['recipient_id' => $userB->id]);

        $this->assertEquals(201, $response->status());
        $conversationId = $response->json('conversation.id');

        // User A sends message
        $response = $this->apiPost("/api/client/conversations/{$conversationId}/messages", $clientKey['raw_key'], $tokenA, ['content' => 'Hello!']);

        $this->assertEquals(201, $response->status());
        $this->assertEquals('Hello!', $response->json('message.content'));

        // User B can see conversation
        $response = $this->apiGet("/api/client/conversations/{$conversationId}", $clientKey['raw_key'], $tokenB);

        $this->assertEquals(200, $response->status());
        $this->assertEquals($conversationId, $response->json('conversation.id'));

        // User B can list messages
        $response = $this->apiGet("/api/client/conversations/{$conversationId}/messages", $clientKey['raw_key'], $tokenB);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Hello!', $response->json('messages.0.content'));

        // User B sends reply
        $response = $this->apiPost("/api/client/conversations/{$conversationId}/messages", $clientKey['raw_key'], $tokenB, ['content' => 'Hi there!']);

        $this->assertEquals(201, $response->status());

        // End conversation
        $response = $this->apiDelete("/api/client/conversations/{$conversationId}", $clientKey['raw_key'], $tokenA);

        $this->assertEquals(200, $response->status());

        // Should not be able to send messages after ended
        $response = $this->apiPost("/api/client/conversations/{$conversationId}/messages", $clientKey['raw_key'], $tokenB, ['content' => 'Late message']);

        $this->assertContains($response->status(), [403, 422]);
    }

    protected function createPhoto(User $user, array $attributes = []): Photo
    {
        return Photo::create(array_merge([
            'user_id' => $user->id,
            'storage_key' => "users/{$user->id}/".(string) Str::uuid().'.webp',
            'mime_type' => 'image/webp',
            'width' => 800,
            'height' => 600,
            'size_bytes' => 50000,
            'visibility' => 'PUBLIC',
            'requires_verified' => false,
            'status' => 'ACTIVE',
        ], $attributes));
    }

    public function test_photos_and_posts_with_privacy(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');

        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);

        $tokenA = $this->login($userA->email, $clientKey['raw_key']);
        $tokenB = $this->login($userB->email, $clientKey['raw_key']);

        // User A creates private photo (directly in DB for authorization testing)
        $photo = $this->createPhoto($userA, [
            'visibility' => 'PRIVATE',
        ]);

        // User B cannot see private photo
        $response = $this->apiGet("/api/client/photos/{$photo->id}", $clientKey['raw_key'], $tokenB);

        $this->assertEquals(403, $response->status());

        // User A can see own photo
        $response = $this->apiGet("/api/client/photos/{$photo->id}", $clientKey['raw_key'], $tokenA);

        $this->assertEquals(200, $response->status());

        // User A creates public post
        $response = $this->apiPost('/api/client/posts', $clientKey['raw_key'], $tokenA, [
            'content' => 'Public post',
            'visibility' => 'PUBLIC',
        ]);

        $this->assertEquals(201, $response->status());
        $postId = $response->json('post.id');

        // User B can see public post
        $response = $this->apiGet("/api/client/posts/{$postId}", $clientKey['raw_key'], $tokenB);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Public post', $response->json('post.content'));

        // User A deletes post
        $response = $this->apiDelete("/api/client/posts/{$postId}", $clientKey['raw_key'], $tokenA);

        $this->assertEquals(200, $response->status());

        // Post should be deleted
        $response = $this->apiGet("/api/client/posts/{$postId}", $clientKey['raw_key'], $tokenA);

        $this->assertEquals(404, $response->status());
    }
}
