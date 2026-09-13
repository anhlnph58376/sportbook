<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\User;
use App\Models\VenueHoliday;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BookingConflictTest extends TestCase
{
    use DatabaseTransactions;

    protected User $player;

    protected Court $court;

    protected function setUp(): void
    {
        parent::setUp();

        $this->player = User::where('email', 'player1@sportbook.vn')->first()
            ?? User::factory()->player()->create();

        $this->court = Court::where('name', 'Sân Bóng Đá 7 Người (Sân A)')->first()
            ?? Court::factory()->create();
    }

    public function test_player_can_create_booking_successfully(): void
    {
        $futureDate = now()->addDays(7)->format('Y-m-d');

        $response = $this->actingAs($this->player, 'sanctum')->postJson('/api/v1/bookings', [
            'court_id' => $this->court->id,
            'booking_date' => $futureDate,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'notes' => 'Trận giao hữu',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'court' => [
                        'id' => $this->court->id,
                    ],
                    'duration_hours' => 2.0,
                    'status' => 'awaiting_payment',
                ],
            ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'booking_date' => $futureDate,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => 'awaiting_payment',
        ]);
    }

    public function test_conflicting_booking_on_same_court_and_slot_returns_409_conflict(): void
    {
        $futureDate = now()->addDays(8)->format('Y-m-d');

        // First player books 14:00 - 16:00
        $firstResponse = $this->actingAs($this->player, 'sanctum')->postJson('/api/v1/bookings', [
            'court_id' => $this->court->id,
            'booking_date' => $futureDate,
            'start_time' => '14:00',
            'end_time' => '16:00',
        ]);
        $firstResponse->assertStatus(201);

        // Second player attempts overlapping slot (15:00 - 17:00)
        $secondPlayer = User::where('email', 'player2@sportbook.vn')->first()
            ?? User::factory()->player()->create();

        $conflictResponse = $this->actingAs($secondPlayer, 'sanctum')->postJson('/api/v1/bookings', [
            'court_id' => $this->court->id,
            'booking_date' => $futureDate,
            'start_time' => '15:00',
            'end_time' => '17:00',
        ]);

        $conflictResponse->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_booking_in_the_past_is_rejected(): void
    {
        $pastDate = now()->subDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->player, 'sanctum')->postJson('/api/v1/bookings', [
            'court_id' => $this->court->id,
            'booking_date' => $pastDate,
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $response->assertStatus(422);
    }

    public function test_booking_on_venue_holiday_is_rejected(): void
    {
        $holidayDate = now()->addDays(5)->format('Y-m-d');

        VenueHoliday::create([
            'venue_id' => $this->court->venue_id,
            'date' => $holidayDate,
            'reason' => 'Nghỉ lễ bảo trì toàn cụm sân',
        ]);

        $response = $this->actingAs($this->player, 'sanctum')->postJson('/api/v1/bookings', [
            'court_id' => $this->court->id,
            'booking_date' => $holidayDate,
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
