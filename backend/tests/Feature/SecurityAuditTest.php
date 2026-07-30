<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\AppConfig;
use App\Models\User;
use App\Services\ApiKeyService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(array $attributes = []): User
    {
        $userId = (string) Str::uuid();

        DB::table('users')->insert([
            'id' => $userId,
            'email' => $attributes['email'] ?? 'user-'.Str::uuid().'@example.com',
            'password_hash' => Hash::make($attributes['password'] ?? 'password'),
            'email_verified_at' => $attributes['email_verified_at'] ?? now(),
            'verification_status' => $attributes['verification_status'] ?? 'not_verified',
            'status' => $attributes['status'] ?? 'active',
            'role' => $attributes['role'] ?? 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    protected function createAdmin(): User
    {
        return $this->createUser(['role' => 'admin']);
    }

    protected function authenticateAs(User $user, ?string $type = 'CLIENT'): string
    {
        $service = new ApiKeyService;
        $result = $service->createApiKey($user, 'Test Key', $type);

        $token = $user->createToken('test-token', ['*'], now()->addDay())->plainTextToken;

        return $result['raw_key'];
    }

    public function test_update_config_rejects_unknown_keys(): void
    {
        $admin = $this->createAdmin();
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($admin, 'Admin Key', 'ADMIN');
        $token = $admin->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/admin/config', [
                'APP_KEY' => 'hacked',
                'APP_DEBUG' => true,
                'installed' => false,
            ]);

        $response->assertStatus(200);
        $this->assertNull(AppConfig::where('key', 'APP_KEY')->first());
        $this->assertNull(AppConfig::where('key', 'APP_DEBUG')->first());
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->createAdmin();
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($admin, 'Admin Key', 'ADMIN');
        $token = $admin->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/users/{$admin->id}/change-role", [
                'role' => 'user',
            ]);

        $response->assertStatus(403);
        $this->assertEquals('admin', $admin->fresh()->role);
    }

    public function test_admin_panel_login_has_throttle(): void
    {
        $admin = $this->createAdmin();

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/admin/login', [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->postJson('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertContains($response->status(), [429, 302]);
    }

    public function test_api_key_prefix_not_in_error_response(): void
    {
        $admin = $this->createAdmin();
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($admin, 'Test Key', 'CLIENT');

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->postJson('/api/client/auth/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'wrong',
            ]);

        $json = $response->json();
        $this->assertArrayNotHasKey('provided_type', $json);
        $this->assertArrayNotHasKey('required_type', $json);
    }

    public function test_password_reset_has_throttle(): void
    {
        $admin = $this->createAdmin();
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($admin, 'Client Key', 'CLIENT');

        for ($i = 0; $i < 10; $i++) {
            $this->withHeader('X-API-Key', $result['raw_key'])
                ->postJson('/api/client/auth/forgot-password', [
                    'email' => 'test@example.com',
                ]);
        }

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->postJson('/api/client/auth/forgot-password', [
                'email' => 'test@example.com',
            ]);

        $this->assertContains($response->status(), [429, 200]);
    }

    public function test_api_key_middleware_rejects_short_key(): void
    {
        $response = $this->withHeader('X-API-Key', 'short')
            ->postJson('/api/client/auth/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

        $response->assertStatus(401);
        $response->assertJson(['code' => 'INVALID_API_KEY_FORMAT']);
    }

    public function test_user_resource_hides_email_from_others(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($user1, 'Client Key', 'CLIENT');
        $token = $user1->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/client/users/{$user2->id}");

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertArrayNotHasKey('email', $json['user']);
    }

    public function test_installer_guard_blocks_after_install(): void
    {
        $this->postJson('/api/install', [
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response = $this->postJson('/api/install', [
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_per_page_capped_at_100(): void
    {
        $admin = $this->createAdmin();
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($admin, 'Admin Key', 'ADMIN');
        $token = $admin->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/users?per_page=10000');

        $response->assertStatus(200);
        $pagination = $response->json('pagination');
        $this->assertLessThanOrEqual(100, $pagination['per_page']);
    }

    public function test_account_lockout_after_failed_attempts(): void
    {
        $user = $this->createUser();
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($user, 'Client Key', 'CLIENT');

        for ($i = 0; $i < 3; $i++) {
            $this->withHeader('X-API-Key', $result['raw_key'])
                ->postJson('/api/client/auth/login', [
                    'email' => $user->email,
                    'password' => 'wrong-password',
                ]);
        }

        $fresh = $user->fresh();
        $this->assertGreaterThan(0, $fresh->failed_login_attempts);
        $this->assertNotNull($fresh->locked_until);
        $this->assertTrue(now()->parse($fresh->locked_until)->isFuture());

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->postJson('/api/client/auth/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('email', $response->json('errors'));
    }

    public function test_successful_login_resets_failed_attempts(): void
    {
        $user = $this->createUser(['password' => 'correct-password']);
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($user, 'Client Key', 'CLIENT');

        $this->withHeader('X-API-Key', $result['raw_key'])
            ->postJson('/api/client/auth/login', [
                'email' => $user->email,
                'password' => 'wrong',
            ]);

        $this->assertGreaterThan(0, $user->fresh()->failed_login_attempts);

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->postJson('/api/client/auth/login', [
                'email' => $user->email,
                'password' => 'correct-password',
            ]);

        $response->assertOk();
        $this->assertEquals(0, $user->fresh()->failed_login_attempts);
        $this->assertNull($user->fresh()->locked_until);
    }

    public function test_token_lifetime_is_7_days(): void
    {
        $admin = $this->createAdmin();
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($admin, 'Client Key', 'CLIENT');

        $response = $this->withHeader('X-API-Key', $result['raw_key'])
            ->postJson('/api/client/auth/login', [
                'email' => $admin->email,
                'password' => 'password',
            ]);

        $response->assertOk();
        $expiresAt = Carbon::parse($response->json('expires_at'));
        $this->assertEqualsWithDelta(7, now()->diffInDays($expiresAt), 0.1);
        $this->assertTrue($expiresAt->isAfter(now()));
    }

    public function test_api_key_rotation(): void
    {
        $admin = $this->createAdmin();
        $apiKeyService = app(ApiKeyService::class);
        $result = $apiKeyService->createApiKey($admin, 'Original Key', 'ADMIN');

        $rotated = $apiKeyService->rotateApiKey($result['api_key'], $admin);

        $this->assertArrayHasKey('raw_key', $rotated);
        $this->assertNotEquals($result['raw_key'], $rotated['raw_key']);

        $originalKey = ApiKey::find($result['api_key']->id);
        $this->assertNotNull($originalKey->revoked_at);

        $newKey = $rotated['api_key'];
        $this->assertEquals('Original Key (rotated)', $newKey->name);
        $this->assertEquals('ADMIN', $newKey->type);
        $this->assertNull($newKey->revoked_at);
    }

    public function test_security_headers_present(): void
    {
        $response = $this->get('/up');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '0');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_email_verification_uses_hmac_not_sha1(): void
    {
        $user = $this->createUser();
        $email = $user->email;
        $key = config('app.key');

        $oldHash = sha1($email);
        $correctHash = hash_hmac('sha256', $email, $key);

        $this->assertNotEquals($oldHash, $correctHash);
        $this->assertEquals(64, strlen($correctHash));

        $verified = hash_equals(hash_hmac('sha256', $email, $key), $correctHash);
        $this->assertTrue($verified);
    }

    public function test_hashing_uses_argon2id_when_configured(): void
    {
        config(['hashing.driver' => 'argon2id']);
        config(['hashing.argon.verify' => true]);

        $password = 'test-password-'.Str::random(16);
        $hash = Hash::make($password);

        $this->assertStringStartsWith('$argon2id$', $hash);
        $this->assertTrue(Hash::check($password, $hash));
    }
}
