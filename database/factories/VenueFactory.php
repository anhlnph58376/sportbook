<?php

namespace Database\Factories;

use App\Enums\VenueStatus;
use App\Enums\VenueVerificationStatus;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company().' Sports Arena';

        return [
            'owner_id' => User::factory()->venueOwner(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'description' => fake()->paragraphs(2, true),
            'address' => fake()->streetAddress(),
            'province' => 'Hồ Chí Minh',
            'district' => fake()->randomElement(['Quận 1', 'Quận 7', 'Quận Bình Thạnh', 'Quận Tân Bình', 'Thủ Đức']),
            'ward' => 'Phường '.fake()->numberBetween(1, 15),
            'latitude' => fake()->latitude(10.70, 10.85),
            'longitude' => fake()->longitude(106.60, 106.75),
            'phone' => '028'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => VenueStatus::Active,
            'verification_status' => VenueVerificationStatus::Approved,
            'rejection_reason' => null,
            'average_rating' => 0.00,
            'total_reviews' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VenueStatus::Draft,
            'verification_status' => VenueVerificationStatus::PendingReview,
        ]);
    }

    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VenueStatus::Draft,
            'verification_status' => VenueVerificationStatus::PendingReview,
        ]);
    }

    public function rejected(string $reason = 'Tài liệu không hợp lệ'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VenueStatus::Inactive,
            'verification_status' => VenueVerificationStatus::Rejected,
            'rejection_reason' => $reason,
        ]);
    }
}
