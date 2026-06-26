<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkSchedule>
 */
class WorkScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'weekday' => fake()->numberBetween(1, 7),
            'type' => 'total_time',
            'expected_minutes' => 480,
            'starts_at' => null,
            'ends_at' => null,
            'effective_from' => today()->toDateString(),
        ];
    }

    /**
     * Indicate that the work schedule uses a start and end time.
     */
    public function timeRange(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'time_range',
            'expected_minutes' => 480,
            'starts_at' => '09:00:00',
            'ends_at' => '18:00:00',
        ]);
    }

    /**
     * Indicate that the work schedule uses only a total expected time.
     */
    public function totalTime(int $expectedMinutes = 480): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'total_time',
            'expected_minutes' => $expectedMinutes,
            'starts_at' => null,
            'ends_at' => null,
        ]);
    }

    /**
     * Indicate that the work schedule is a day off.
     */
    public function dayOff(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'day_off',
            'expected_minutes' => 0,
            'starts_at' => null,
            'ends_at' => null,
        ]);
    }

    /**
     * Set the weekday for the work schedule.
     */
    public function forWeekday(int $weekday): static
    {
        return $this->state(fn (array $attributes) => [
            'weekday' => $weekday,
        ]);
    }
}
