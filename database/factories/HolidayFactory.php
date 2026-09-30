<?php

namespace Database\Factories;

use App\Models\Holiday;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => CarbonImmutable::instance($this->faker->dateTimeBetween('+1 day', '+1 year'))->toDateString(),
            'country_code' => strtoupper($this->faker->randomElement(['BR', 'US', 'PT', 'AR'])),
            'is_national' => $this->faker->boolean(),
            'name' => $this->faker->words(3, true),
        ];
    }
}
