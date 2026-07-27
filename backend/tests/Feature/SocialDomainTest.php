<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Friendship;
use App\Models\FriendshipRequest;
use App\Models\GeoPolygon;
use App\Models\GeoZone;
use App\Models\Toke;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\ApiKeyService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SocialDomainTest extends TestCase
{
    use RefreshDatabase;

protected function setUp(): void
    {
        parent::setUp();
        
        // Create global zone for tests directly in the transaction
        $adminId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $adminId,
            'email' => 'geo-admin@example.com',
            'password_hash' => bcrypt('password'),
            'email_verified_at' => now(),
            'verification_status' => 'not_verified',
            'status' => 'active',
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->zoneId = (string) Str::uuid();
        
        // Use DB insert to avoid model boot issues
        DB::table('geo_zones')->insert([
            'id' => $this->zoneId,
            'name' => 'Global Test Zone',
            'description' => 'Global zone for tests',
            'is_active' => true,
            'created_by' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Polygon covering most of the world (avoiding antipodal edges)
        DB::table('geo_polygons')->insert([
            'id' => (string) Str::uuid(),
            'zone_id' => $this->zoneId,
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
            'email' => $attributes['email'] ?? 'user-' . Str::uuid() . '@example.com',
            'password_hash' => bcrypt('password'),
            'email_verified_at' => now(),
            'verification_status' => 'not_verified',
            'status' => 'active',
            'role' => $attributes['role'] ?? 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('profiles')->insert([
            'user_id' => $userId,
            'location' => DB::raw("ST_SetSRID(ST_MakePoint(0, 0), 4326)"),
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
        $service = new ApiKeyService();
        return $service->createApiKey($creator, "Test {$type} Key", $type);
    }

    protected function getHeaders(string $apiKey): array
    {
        return ['X-API-Key' => $apiKey];
    }

public function test_toke_flow_complete_lifecycle(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');
        
        // Create two users
        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);
        
        // User A logs in
        $loginA = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userA->email, 'password' => 'password']);
        
        dump($loginA->status(), $loginA->json());
        
        $this->assertEquals(200, $loginA->status());
        $tokenA = $loginA->json('token');
        
        // User B logs in
        $loginB = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userB->email, 'password' => 'password']);
        
        dump($loginB->status(), $loginB->json());
        
        $this->assertEquals(200, $loginB->status());
        $tokenB = $loginB->json('token');
        
        // User A sends toke to User B
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson('/api/client/tokes', ['receiver_id' => $userB->id]);
        
        dump($response->status(), $response->json());
        
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
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->getJson('/api/client/tokes');
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals($tokeId, $response->json('received.data.0.id'));
        
        // User B consumes toke (creates match)
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->postJson("/api/client/tokes/{$tokeId}/consume");
        
        $this->assertEquals(200, $response->status());
        $this->assertTrue($response->json('match_created'));
        $matchId = $response->json('toke.id');
        
        // Verify match was created
        $this->assertDatabaseHas('matches', [
            'id' => $matchId,
            'status' => 'ACTIVE',
        ]);
        
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
        
        // Create users and mutual toke -> match
        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);
        
        $loginA = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userA->email, 'password' => 'password']);
        $tokenA = $loginA->json('token');
        
        $loginB = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userB->email, 'password' => 'password']);
        $tokenB = $loginB->json('token');
        
        // A -> B
        $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson('/api/client/tokes', ['receiver_id' => $userB->id]);
        
        // B -> A (mutual)
        $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->postJson('/api/client/tokes', ['receiver_id' => $userA->id]);
        
        // Consume to create match
        $tokes = Toke::where('sender_id', $userB->id)
            ->where('receiver_id', $userA->id)
            ->where('status', 'ACTIVE')
            ->get();
        $toke = $tokes->first();
        
        $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson("/api/client/tokes/{$toke->id}/consume");
        
        // Find match
        $match = UserMatch::between($userA, $userB)->active()->first();
        $this->assertNotNull($match);
        
        // Convert match to friendship
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson("/api/client/matches/{$match->id}/convert-to-friendship");
        
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
        
        $loginA = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userA->email, 'password' => 'password']);
        $tokenA = $loginA->json('token');
        
        $loginB = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userB->email, 'password' => 'password']);
        $tokenB = $loginB->json('token');
        
        // User A sends friendship request to User B
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson('/api/client/friendships', ['addressee_id' => $userB->id]);
        
        $this->assertEquals(201, $response->status());
        $requestId = $response->json('friendship_request.id');
        
        // User B can see received request
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->getJson('/api/client/friendship-requests');
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals($requestId, $response->json('received.data.0.id'));
        
        // User B accepts
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->postJson("/api/client/friendship-requests/{$requestId}/accept");
        
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
        
        $loginA = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userA->email, 'password' => 'password']);
        $tokenA = $loginA->json('token');
        
        $loginB = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userB->email, 'password' => 'password']);
        $tokenB = $loginB->json('token');
        
        // User A blocks User B
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson('/api/client/blocks', ['blocked_id' => $userB->id]);
        
        $this->assertEquals(201, $response->status());
        
        // User A should not be able to send toke to User B
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson('/api/client/tokes', ['receiver_id' => $userB->id]);
        
        $this->assertEquals(403, $response->status());
        
        // User B should not be able to send toke to User A
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->postJson('/api/client/tokes', ['receiver_id' => $userA->id]);
        
        $this->assertEquals(403, $response->status());
        
        // User B should not be able to send friendship request
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->postJson('/api/client/friendships', ['addressee_id' => $userA->id]);
        
        $this->assertEquals(403, $response->status());
    }

    public function test_conversation_and_messages(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');
        
        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);
        
        $loginA = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userA->email, 'password' => 'password']);
        $tokenA = $loginA->json('token');
        
        $loginB = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userB->email, 'password' => 'password']);
        $tokenB = $loginB->json('token');
        
        // Create conversation
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson('/api/client/conversations', ['recipient_id' => $userB->id]);
        
        $this->assertEquals(201, $response->status());
        $conversationId = $response->json('conversation.id');
        
        // User A sends message
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson("/api/client/conversations/{$conversationId}/messages", ['content' => 'Hello!']);
        
        $this->assertEquals(201, $response->status());
        $this->assertEquals('Hello!', $response->json('message.content'));
        
        // User B can see conversation
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->getJson("/api/client/conversations/{$conversationId}");
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals($conversationId, $response->json('conversation.id'));
        
        // User B can list messages
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->getJson("/api/client/conversations/{$conversationId}/messages");
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals(1, $response->json('messages.data.0.content'));
        
        // User B sends reply
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->postJson("/api/client/conversations/{$conversationId}/messages", ['content' => 'Hi there!']);
        
        $this->assertEquals(201, $response->status());
        
        // End conversation
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->deleteJson("/api/client/conversations/{$conversationId}");
        
        $this->assertEquals(200, $response->status());
        
        // Should not be able to send messages after ended
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->postJson("/api/client/conversations/{$conversationId}/messages", ['content' => 'Late message']);
        
        $this->assertEquals(422, $response->status());
    }

    public function test_photos_and_posts_with_privacy(): void
    {
        $admin = $this->createAdmin();
        $clientKey = $this->createApiKey($admin, 'CLIENT');
        
        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);
        
        $loginA = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userA->email, 'password' => 'password']);
        $tokenA = $loginA->json('token');
        
        $loginB = $this->withHeaders($this->getHeaders($clientKey['raw_key']))
            ->postJson('/api/client/auth/login', ['email' => $userB->email, 'password' => 'password']);
        $tokenB = $loginB->json('token');
        
        // User A creates private photo
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson('/api/client/photos', [
            'url' => 'https://example.com/photo.jpg',
            'visibility' => 'PRIVATE',
            'requires_verified' => false,
        ]);
        
        $this->assertEquals(201, $response->status());
        $photoId = $response->json('photo.id');
        
        // User B cannot see private photo
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->getJson("/api/client/photos/{$photoId}");
        
        $this->assertEquals(403, $response->status());
        
        // User A can see own photo
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->getJson("/api/client/photos/{$photoId}");
        
        $this->assertEquals(200, $response->status());
        
        // User A creates public post
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->postJson('/api/client/posts', [
            'content' => 'Public post',
            'visibility' => 'PUBLIC',
        ]);
        
        $this->assertEquals(201, $response->status());
        $postId = $response->json('post.id');
        
        // User B can see public post
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenB,
        ])->getJson("/api/client/posts/{$postId}");
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Public post', $response->json('post.content'));
        
        // User A deletes post
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->deleteJson("/api/client/posts/{$postId}");
        
        $this->assertEquals(200, $response->status());
        
        // Post should be deleted
        $response = $this->withHeaders([
            'X-API-Key' => $clientKey['raw_key'],
            'Authorization' => 'Bearer ' . $tokenA,
        ])->getJson("/api/client/posts/{$postId}");
        
        $this->assertEquals(403, $response->status());
    }
}