<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Enums\VenueVerificationStatus;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'admin@sportbook.vn')->firstOrFail();
        $this->player = User::where('email', 'player1@sportbook.vn')->firstOrFail();
    }

    public function test_admin_can_view_metrics(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/v1/admin/metrics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'total_users',
                    'total_venues',
                    'active_venues',
                    'pending_venues',
                    'total_courts',
                    'total_bookings',
                    'total_revenue_vnd',
                ],
            ]);
    }

    public function test_admin_can_approve_pending_venue(): void
    {
        $venue = Venue::where('slug', 'clb-the-thao-binh-thanh-club')->firstOrFail();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/venues/{$venue->id}/approve", [
                'action' => 'approve',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'verification_status' => VenueVerificationStatus::Approved->value,
            'status' => VenueStatus::Active->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'APPROVE_VENUE',
            'auditable_id' => $venue->id,
        ]);
    }

    public function test_admin_can_lock_and_unlock_user(): void
    {
        $targetUser = User::where('email', 'player2@sportbook.vn')->firstOrFail();

        // Lock user
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/users/{$targetUser->id}/toggle-lock");

        $response->assertStatus(200);
        $this->assertEquals(UserStatus::Locked, $targetUser->fresh()->status);

        // Unlock user
        $response2 = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/users/{$targetUser->id}/toggle-lock");

        $response2->assertStatus(200);
        $this->assertEquals(UserStatus::Active, $targetUser->fresh()->status);
    }

    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $response = $this->actingAs($this->player, 'sanctum')->getJson('/api/v1/admin/metrics');

        $response->assertStatus(403);
    }
}
