<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BookingCancellationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $player;

    protected Court $court;

    protected function setUp(): void
    {
        parent::setUp();

        $this->player = User::where('email', 'player1@sportbook.vn')->firstOrFail();
        $this->court = Court::where('name', 'Sân Bóng Đá 7 Người (Sân A)')->firstOrFail();
    }

    public function test_player_can_cancel_booking_more_than_24_hours_before_and_get_full_refund(): void
    {
        $futureDate = now()->addDays(5)->format('Y-m-d');

        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'booking_code' => 'SB-TEST-CANCEL100',
            'booking_date' => $futureDate,
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
            'duration_hours' => 2.0,
            'total_price' => 800000.00,
            'deposit_amount' => 240000.00,
            'deposit_percentage' => 30,
            'status' => BookingStatus::Confirmed,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => 'TXN-CANCEL100',
            'amount' => 240000.00,
            'payment_method' => 'momo',
            'status' => PaymentStatus::Completed,
            'paid_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/cancel", [
                'reason' => 'Bận việc gia đình đột xuất',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'refund_amount' => 240000,
                    'refund_percentage' => 100,
                    'booking' => [
                        'status' => 'cancelled',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
            'refund_amount' => 240000.00,
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'refunded',
            'refund_amount' => 240000.00,
        ]);
    }

    public function test_player_can_cancel_booking_between_12_and_24_hours_and_get_half_refund(): void
    {
        // 18 hours in the future
        $futureTime = now()->addHours(18);

        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'booking_code' => 'SB-TEST-CANCEL50',
            'booking_date' => $futureTime->format('Y-m-d'),
            'start_time' => $futureTime->format('H:i:s'),
            'end_time' => $futureTime->copy()->addHours(2)->format('H:i:s'),
            'duration_hours' => 2.0,
            'total_price' => 800000.00,
            'deposit_amount' => 240000.00,
            'deposit_percentage' => 30,
            'status' => BookingStatus::Confirmed,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => 'TXN-CANCEL50',
            'amount' => 240000.00,
            'payment_method' => 'mock',
            'status' => PaymentStatus::Completed,
            'paid_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/cancel", [
                'reason' => 'Đội bóng thiếu người',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'refund_amount' => 120000,
                    'refund_percentage' => 50,
                ],
            ]);
    }

    public function test_player_cannot_cancel_another_players_booking(): void
    {
        $otherPlayer = User::where('email', 'player2@sportbook.vn')->firstOrFail();
        $futureDate = now()->addDays(4)->format('Y-m-d');

        $booking = Booking::create([
            'user_id' => $otherPlayer->id,
            'court_id' => $this->court->id,
            'booking_code' => 'SB-TEST-OTHER',
            'booking_date' => $futureDate,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'duration_hours' => 2.0,
            'total_price' => 500000.00,
            'deposit_amount' => 150000.00,
            'deposit_percentage' => 30,
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/cancel", [
                'reason' => 'Thử hủy lén của người khác',
            ]);

        $response->assertStatus(403);
    }
}
