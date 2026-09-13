<?php

namespace Database\Factories;

use App\Enums\CourtStatus;
use App\Models\Court;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
{
    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'sport_id' => Sport::factory(),
            'name' => 'Sân số '.fake()->numberBetween(1, 10),
            'description' => fake()->sentence(),
            'capacity' => fake()->randomElement([2, 4, 10, 14]),
            'status' => CourtStatus::Active,
        ];
    }

    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CourtStatus::UnderMaintenance,
        ]);
    }
}
