<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BookingExpirationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unpaid_expired_booking_is_marked_as_expired_by_scheduler_command(): void
    {
        $player = User::where('email', 'player1@sportbook.vn')->firstOrFail();
        $court = Court::firstOrFail();

        // 1. Expired booking (deadline in past)
        $expiredBooking = Booking::create([
            'user_id' => $player->id,
            'court_id' => $court->id,
            'booking_code' => 'SB-TEST-EXPIRE01',
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'duration_hours' => 2.0,
            'total_price' => 400000.00,
            'deposit_amount' => 120000.00,
            'deposit_percentage' => 30,
            'status' => BookingStatus::AwaitingPayment,
            'expires_at' => now()->subMinutes(10), // expired 10 mins ago
        ]);

        // 2. Active booking (deadline in future)
        $activeBooking = Booking::create([
            'user_id' => $player->id,
            'court_id' => $court->id,
            'booking_code' => 'SB-TEST-ACTIVE01',
            'booking_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'duration_hours' => 2.0,
            'total_price' => 400000.00,
            'deposit_amount' => 120000.00,
            'deposit_percentage' => 30,
            'status' => BookingStatus::AwaitingPayment,
            'expires_at' => now()->addMinutes(20), // 20 mins remaining
        ]);

        // Run the scheduled artisan command
        $this->artisan('sportbook:expire-bookings')
            ->expectsOutputToContain('Completed.')
            ->assertExitCode(0);

        // Verify status changes
        $this->assertEquals(BookingStatus::Expired, $expiredBooking->fresh()->status);
        $this->assertEquals(BookingStatus::AwaitingPayment, $activeBooking->fresh()->status);
    }
}
