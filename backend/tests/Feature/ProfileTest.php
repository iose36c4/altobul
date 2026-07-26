<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Domain\Authorization\VisibilityLevel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(array $attributes = []): User
    {
        $userId = (string) Str::uuid();
        
        DB::table('users')->insert([
            'id' => $userId,
            'email' => $attributes['email'] ?? 'test-' . Str::uuid() . '@example.com',
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

    protected function createProfile(User $user, array $attributes = []): Profile
    {
        return Profile::create(array_merge([
            'user_id' => $user->id,
            'location' => \DB::raw("ST_SetSRID(ST_MakePoint(0, 0), 4326)"),
            'location_precision_meters' => 1000,
            'discoverable' => true,
            'profile_visibility' => 'PUBLIC',
        ], $attributes));
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = $this->createUser();
        $this->createProfile($user);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'profile' => [
                    'user_id',
                    'title',
                    'description',
                    'birth_date',
                    'profile_visibility',
                    'title_visibility',
                    'description_visibility',
                    'birth_date_visibility',
                    'discoverable',
                ],
            ]);
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = $this->createUser();
        $this->createProfile($user);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'title' => 'Software Developer',
                'description' => 'I love coding',
                'profile_visibility' => 'FRIENDS',
                'title_visibility' => 'MATCH',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'profile' => [
                    'title' => 'Software Developer',
                    'description' => 'I love coding',
                    'profile_visibility' => 'FRIENDS',
                    'title_visibility' => 'MATCH',
                ],
            ]);
    }

    public function test_user_cannot_update_another_users_profile(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        
        $this->createProfile($user1);

        $response = $this->actingAs($user2, 'sanctum')
            ->putJson('/api/profile', [
                'title' => 'Hacker',
            ]);

        $response->assertStatus(403);
    }

    public function test_profile_privacy_enforced_on_view(): void
    {
        $owner = $this->createUser();
        $viewer = $this->createUser();
        
        $this->createProfile($owner, [
            'profile_visibility' => 'PRIVATE',
            'title' => 'Secret Title',
            'title_visibility' => 'PRIVATE',
        ]);

        // Viewer without relationship cannot see private profile
        $response = $this->actingAs($viewer, 'sanctum')
            ->getJson("/api/users/{$owner->id}");

        $response->assertStatus(403);
    }
}