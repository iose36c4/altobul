<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\User;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiKeyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(array $attributes = []): User
    {
        $userId = (string) Str::uuid();

        DB::table('users')->insert([
            'id' => $userId,
            'email' => $attributes['email'] ?? 'admin-'.Str::uuid().'@example.com',
            'password_hash' => bcrypt('password'),
            'email_verified_at' => now(),
            'verification_status' => 'not_verified',
            'status' => 'active',
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    protected function createUser(array $attributes = []): User
    {
        $userId = (string) Str::uuid();

        DB::table('users')->insert([
            'id' => $userId,
            'email' => $attributes['email'] ?? 'user-'.Str::uuid().'@example.com',
            'password_hash' => bcrypt('password'),
            'email_verified_at' => now(),
            'verification_status' => 'not_verified',
            'status' => 'active',
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    public function test_client_key_can_access_client_endpoint(): void
    {
        $admin = $this->createAdmin();
        $service = new ApiKeyService;
        $result = $service->createApiKey($admin, 'Test Client Key', 'CLIENT');

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->getJson('/api/test-api-key');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('API key middleware passed', $response->json('message'));
    }

    public function test_admin_key_can_access_admin_endpoint(): void
    {
        $admin = $this->createAdmin();
        $service = new ApiKeyService;
        $result = $service->createApiKey($admin, 'Test Admin Key', 'ADMIN');

        // Test admin key on admin endpoint that only requires api.key (POST for login)
        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->postJson('/api/admin/auth/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

        // 422 = invalid credentials, but API key middleware passed
        $this->assertEquals(422, $response->status());
    }

    public function test_client_key_cannot_access_admin_endpoint(): void
    {
        $admin = $this->createAdmin();
        $service = new ApiKeyService;
        $result = $service->createApiKey($admin, 'Test Client Key', 'CLIENT');

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->postJson('/api/admin/auth/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

        $this->assertEquals(403, $response->status());
        $this->assertEquals('API key type mismatch', $response->json('error'));
    }

    public function test_admin_key_can_access_client_endpoint(): void
    {
        $admin = $this->createAdmin();
        $service = new ApiKeyService;
        $result = $service->createApiKey($admin, 'Test Admin Key', 'ADMIN');

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->getJson('/api/test-api-key');

        $this->assertEquals(403, $response->status());
        $this->assertEquals('API key type mismatch', $response->json('error'));
    }

    public function test_invalid_api_key_rejected(): void
    {
        $response = $this->withHeader('X-API-Key', 'invalid_key')
            ->getJson('/api/test-api-key');

        $this->assertEquals(401, $response->status());
        $this->assertEquals('INVALID_API_KEY', $response->json('code'));
    }

    public function test_revoked_api_key_rejected(): void
    {
        $admin = $this->createAdmin();
        $service = new ApiKeyService;
        $result = $service->createApiKey($admin, 'Test Key', 'CLIENT');

        $apiKey = ApiKey::where('key_prefix', substr($result['raw_key'], 0, 8))->first();
        $apiKey->revoke();

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->getJson('/api/test-api-key');

        $this->assertEquals(401, $response->status());
        $this->assertEquals('API_KEY_REVOKED', $response->json('code'));
    }

    public function test_expired_api_key_rejected(): void
    {
        $admin = $this->createAdmin();

        // Create expired key directly in DB
        $rawKey = 'ab_cli_'.Str::random(32);
        $prefix = substr($rawKey, 0, 8);

        ApiKey::create([
            'name' => 'Expired Key',
            'type' => 'CLIENT',
            'key_hash' => bcrypt($rawKey),
            'key_prefix' => $prefix,
            'expires_at' => now()->subDay(),
            'created_by' => $admin->id,
        ]);

        $response = $this->withHeader('X-API-Key', $rawKey)
            ->getJson('/api/test-api-key');

        $this->assertEquals(401, $response->status());
        $this->assertEquals('API_KEY_EXPIRED', $response->json('code'));
    }

    public function test_missing_api_key_rejected(): void
    {
        $response = $this->getJson('/api/test-api-key');

        $this->assertEquals(401, $response->status());
        $this->assertEquals('MISSING_API_KEY', $response->json('code'));
    }

    public function test_api_key_usage_tracked(): void
    {
        $admin = $this->createAdmin();
        $service = new ApiKeyService;
        $result = $service->createApiKey($admin, 'Test Key', 'CLIENT');

        $this->withHeader('X-API-Key', $result['raw_key'])
            ->getJson('/api/test-api-key');

        $apiKey = ApiKey::where('key_prefix', substr($result['raw_key'], 0, 8))->first();

        $this->assertNotNull($apiKey->last_used_at);
    }

    public function test_api_key_model_validation(): void
    {
        $admin = $this->createAdmin();
        $service = new ApiKeyService;
        $result = $service->createApiKey($admin, 'Test Key', 'CLIENT');

        $apiKey = ApiKey::where('key_prefix', substr($result['raw_key'], 0, 8))->first();

        $this->assertEquals('CLIENT', $apiKey->type);
        $this->assertEquals('Test Key', $apiKey->name);
        $this->assertTrue($apiKey->isValid());
        $this->assertFalse($apiKey->isRevoked());
        $this->assertFalse($apiKey->isExpired());
        $this->assertEquals($admin->id, $apiKey->created_by);
    }

    public function test_api_key_revocation(): void
    {
        $admin = $this->createAdmin();
        $service = new ApiKeyService;
        $result = $service->createApiKey($admin, 'Test Key', 'CLIENT');

        $apiKey = ApiKey::where('key_prefix', substr($result['raw_key'], 0, 8))->first();
        $apiKey->revoke();

        $this->assertTrue($apiKey->isRevoked());
        $this->assertFalse($apiKey->isValid());
    }

    public function test_api_key_prefix_extraction(): void
    {
        $rawKey = 'ab_cli_abcdefghijklmnopqrstuvwxyz123456';
        $prefix = ApiKey::extractPrefix($rawKey);

        $this->assertEquals('ab_cli_a', $prefix);
    }
}
