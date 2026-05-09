<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'api@sacca.in',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api@sacca.in',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'user' => ['id', 'name', 'email', 'role']],
            ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_api_login_returns_422_for_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'api@sacca.in']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'api@sacca.in',
            'password' => 'wrong',
        ])->assertUnprocessable();
    }

    public function test_api_login_returns_403_for_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'inactive@sacca.in',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@sacca.in',
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_api_me_returns_user_details(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_api_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_api_logout_deletes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseEmpty('personal_access_tokens');
    }

    public function test_api_fcm_token_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/fcm-token', ['fcm_token' => 'test-fcm-token-123'])
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => 'test-fcm-token-123',
        ]);
    }
}
