<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use RefreshDatabase;

    public function test_installer_shows_not_installed(): void
    {
        $response = $this->getJson('/api/install');

        $this->assertEquals(200, $response->status());
        $this->assertFalse($response->json('installed'));
        $this->assertTrue($response->json('requires_installation'));
    }

    public function test_installer_status_checks(): void
    {
        $response = $this->getJson('/api/install/status');

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('checks', $response->json());
        $this->assertArrayHasKey('ready', $response->json());
    }

    public function test_first_admin_can_be_registered(): void
    {
        $response = $this->postJson('/api/install', [
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'app_name' => 'Test App',
        ]);

        $this->assertEquals(201, $response->status());
        $this->assertEquals('Backend installed successfully', $response->json('message'));
        $this->assertEquals('admin@example.com', $response->json('admin.email'));
        $this->assertEquals('admin', $response->json('admin.role'));

        $this->assertNotNull($response->json('api_keys.client.raw_key'));
        $this->assertNotNull($response->json('api_keys.admin.raw_key'));
        $this->assertEquals('CLIENT', $response->json('api_keys.client.type'));
        $this->assertEquals('ADMIN', $response->json('api_keys.admin.type'));
    }

    public function test_installer_rejects_invalid_data(): void
    {
        $response = $this->postJson('/api/install', [
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $this->assertEquals(422, $response->status());
        $this->assertArrayHasKey('errors', $response->json());
    }

    public function test_installer_cannot_be_repeated(): void
    {
        // First install
        $this->postJson('/api/install', [
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Second attempt should fail
        $response = $this->postJson('/api/install', [
            'email' => 'admin2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertEquals(400, $response->status());
        $this->assertEquals('Already installed', $response->json('error'));
    }

    public function test_installed_backend_shows_installed(): void
    {
        // Install first
        $this->postJson('/api/install', [
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Check status
        $response = $this->getJson('/api/install');
        $this->assertEquals(200, $response->status());
        $this->assertTrue($response->json('installed'));
        $this->assertFalse($response->json('requires_installation'));
    }

    public function test_generated_api_keys_work(): void
    {
        // Install
        $installResponse = $this->postJson('/api/install', [
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $clientKey = $installResponse->json('api_keys.client.raw_key');
        $adminKey = $installResponse->json('api_keys.admin.raw_key');

        // Test client key on client test endpoint
        $response = $this->withHeader('X-API-Key', $clientKey)
            ->getJson('/api/test-api-key');
        $this->assertEquals(200, $response->status());

        // Test admin key on admin test endpoint - login should work with correct credentials
        $response = $this->withHeader('X-API-Key', $adminKey)
            ->postJson('/api/admin/auth/login', [
                'email' => 'admin@example.com',
                'password' => 'password123',
            ]);
        $this->assertEquals(200, $response->status());
        $this->assertNotNull($response->json('token'));
    }
}
