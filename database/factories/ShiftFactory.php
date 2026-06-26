<?php

namespace Database\Factories;

use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = CarbonImmutable::today()->setTime(9, 0);

        return [
            'user_id' => User::factory(),
            'started_at' => $startedAt,
            'ended_at' => $startedAt->addHours(8),
        ];
    }

    /**
     * Indicate that the shift is ongoing.
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => CarbonImmutable::now()->subHours(2),
            'ended_at' => null,
        ]);
    }

    /**
     * Indicate that the shift crosses midnight.
     */
    public function overnight(): static
    {
        $startedAt = CarbonImmutable::yesterday()->setTime(22, 0);

        return $this->state(fn (array $attributes) => [
            'started_at' => $startedAt,
            'ended_at' => $startedAt->addHours(8),
        ]);
    }
}
