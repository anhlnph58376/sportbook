<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory()->completed(),
            'user_id' => fn (array $attributes) => Booking::find($attributes['booking_id'])->user_id ?? User::factory(),
            'venue_id' => fn (array $attributes) => Booking::find($attributes['booking_id'])->court->venue_id ?? Venue::factory(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->randomElement([
                'Sân rất đẹp, mặt thảm tốt và sạch sẽ.',
                'Ánh sáng đầy đủ, phục vụ nhiệt tình chu đáo.',
                'Sân thoáng mát, nước uống và phòng thay đồ tiện nghi.',
                'Giá cả hợp lý, sẽ quay lại thường xuyên.',
            ]),
            'is_visible' => true,
            'reported_count' => 0,
        ];
    }
}
