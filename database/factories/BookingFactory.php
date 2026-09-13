<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('now', '+14 days')->format('Y-m-d');
        $startHour = fake()->numberBetween(7, 20);
        $startTime = sprintf('%02d:00:00', $startHour);
        $endTime = sprintf('%02d:00:00', $startHour + 1);
        $totalPrice = 200000.00;
        $depositPercentage = 30;
        $depositAmount = ($totalPrice * $depositPercentage) / 100;

        return [
            'user_id' => User::factory()->player(),
            'court_id' => Court::factory(),
            'booking_code' => 'SB-'.date('Ymd').'-'.strtoupper(Str::random(6)),
            'booking_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_hours' => 1.0,
            'total_price' => $totalPrice,
            'deposit_amount' => $depositAmount,
            'deposit_percentage' => $depositPercentage,
            'status' => BookingStatus::Pending,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'refund_amount' => null,
            'expires_at' => now()->addMinutes(30),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Confirmed,
            'expires_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Completed,
            'expires_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Khách bận đột xuất',
            'refund_amount' => 60000.00,
        ]);
    }
}
