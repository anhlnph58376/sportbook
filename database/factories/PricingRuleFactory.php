<?php

namespace Database\Factories;

use App\Enums\DayType;
use App\Models\Court;
use App\Models\PricingRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingRule>
 */
class PricingRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'court_id' => Court::factory(),
            'name' => 'Giá tiêu chuẩn',
            'start_time' => '06:00:00',
            'end_time' => '22:00:00',
            'price_per_hour' => fake()->randomElement([150000, 200000, 250000, 300000]),
            'day_type' => DayType::All,
            'is_active' => true,
        ];
    }
}
