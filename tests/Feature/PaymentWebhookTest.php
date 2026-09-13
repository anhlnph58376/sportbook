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

class PaymentWebhookTest extends TestCase
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

    public function test_player_can_initiate_checkout_for_booking(): void
    {
        $futureDate = now()->addDays(6)->format('Y-m-d');

        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'booking_code' => 'SB-TEST-PAY01',
            'booking_date' => $futureDate,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'duration_hours' => 2.0,
            'total_price' => 500000.00,
            'deposit_amount' => 150000.00,
            'deposit_percentage' => 30,
            'status' => BookingStatus::AwaitingPayment,
            'expires_at' => now()->addMinutes(30),
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 150000.00,
            'payment_method' => 'mock',
            'status' => PaymentStatus::Pending,
        ]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/checkout", [
                'provider' => 'mock',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'provider' => 'mock',
                    'amount' => 150000,
                ],
            ]);
    }

    public function test_mock_payment_webhook_confirms_booking_on_success(): void
    {
        $futureDate = now()->addDays(6)->format('Y-m-d');

        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'booking_code' => 'SB-TEST-PAY02',
            'booking_date' => $futureDate,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'duration_hours' => 2.0,
            'total_price' => 500000.00,
            'deposit_amount' => 150000.00,
            'deposit_percentage' => 30,
            'status' => BookingStatus::AwaitingPayment,
            'expires_at' => now()->addMinutes(30),
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 150000.00,
            'payment_method' => 'mock',
            'status' => PaymentStatus::Pending,
        ]);

        $response = $this->postJson('/api/v1/payments/mock/checkout', [
            'payment_id' => $payment->id,
            'status' => 'success',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'booking' => [
                        'status' => 'confirmed',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
            'expires_at' => null,
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);
    }
}
