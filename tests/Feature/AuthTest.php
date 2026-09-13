<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_register_as_player(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Nguyễn Test Player',
            'email' => 'newplayer@sportbook.vn',
            'phone' => '0912334455',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'player',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'newplayer@sportbook.vn',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newplayer@sportbook.vn',
            'status' => 'active',
        ]);
    }

    public function test_user_can_register_as_venue_owner(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Trần Test Owner',
            'email' => 'newowner@sportbook.vn',
            'phone' => '0988776655',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'venue_owner',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'newowner@sportbook.vn')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isVenueOwner());
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'player1@sportbook.vn',
            'password' => 'Password@123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'access_token',
                ],
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'player1@sportbook.vn',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_locked_user_cannot_login(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'locked@sportbook.vn',
            'password' => 'Password@123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_authenticated_user_can_retrieve_profile(): void
    {
        $user = User::where('email', 'player1@sportbook.vn')->first();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'player1@sportbook.vn',
                ],
            ]);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::where('email', 'player1@sportbook.vn')->first();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/profile', [
            'name' => 'Nguyễn Tuấn Anh Updated',
            'phone' => '0987654321',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Nguyễn Tuấn Anh Updated',
                    'phone' => '0987654321',
                ],
            ]);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::where('email', 'player2@sportbook.vn')->first();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/password', [
            'current_password' => 'Password@123',
            'password' => 'NewSecretPassword@123',
            'password_confirmation' => 'NewSecretPassword@123',
        ]);

        $response->assertStatus(200);

        // Verify login with new password
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'player2@sportbook.vn',
            'password' => 'NewSecretPassword@123',
        ]);

        $loginResponse->assertStatus(200);
    }
}
