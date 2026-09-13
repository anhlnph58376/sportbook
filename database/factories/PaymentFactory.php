<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'transaction_id' => 'TXN-'.strtoupper(Str::random(12)),
            'amount' => 60000.00,
            'payment_method' => fake()->randomElement(['mock', 'momo', 'vnpay']),
            'status' => PaymentStatus::Pending,
            'provider_response' => null,
            'paid_at' => null,
            'refunded_at' => null,
            'refund_amount' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Completed,
            'paid_at' => now(),
            'provider_response' => ['resultCode' => 0, 'message' => 'Success'],
        ]);
    }
}
