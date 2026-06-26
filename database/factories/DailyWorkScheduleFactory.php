<?php

namespace Database\Factories;

use App\Models\DailyWorkSchedule;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyWorkSchedule>
 */
class DailyWorkScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = CarbonImmutable::today()->addDays(fake()->numberBetween(-7, 7));

        return [
            'user_id' => User::factory(),
            'work_schedule_id' => null,
            'date' => $date->toDateString(),
            'weekday' => $date->dayOfWeekIso,
            'type' => 'total_time',
            'expected_minutes' => 480,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    /**
     * Indicate that the daily work schedule uses a start and end time.
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
     * Indicate that the daily work schedule is a day off.
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
     * Build a daily snapshot from a work schedule version.
     */
    public function fromWorkSchedule(WorkSchedule $workSchedule, DateTimeInterface|string|null $date = null): static
    {
        $snapshotDate = match (true) {
            $date instanceof DateTimeInterface => CarbonImmutable::instance($date),
            is_string($date) => CarbonImmutable::parse($date),
            default => CarbonImmutable::today()
                ->startOfWeek(CarbonInterface::MONDAY)
                ->addDays($workSchedule->weekday - 1),
        };

        return $this->state(fn (array $attributes) => [
            'user_id' => $workSchedule->user_id,
            'work_schedule_id' => $workSchedule->id,
            'date' => $snapshotDate->toDateString(),
            'weekday' => $snapshotDate->dayOfWeekIso,
            'type' => $workSchedule->type,
            'expected_minutes' => $workSchedule->expected_minutes,
            'starts_at' => $workSchedule->starts_at,
            'ends_at' => $workSchedule->ends_at,
        ]);
    }
}
