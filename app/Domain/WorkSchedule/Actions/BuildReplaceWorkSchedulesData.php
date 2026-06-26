<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Domain\WorkSchedule\DTOs\ReplaceWorkScheduleEntryData;
use App\Domain\WorkSchedule\DTOs\ReplaceWorkSchedulesData;
use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Models\User;
use App\Support\Parsing\DateParser;

final class BuildReplaceWorkSchedulesData
{
    /**
     * @param array{
     *     effective_from: string,
     *     schedules: array<int, array{
     *         weekday: int,
     *         type: string,
     *         expected_minutes?: int|null,
     *         starts_at?: string|null,
     *         ends_at?: string|null
     *     }>
     * } $validated
     */
    public function __invoke(User $user, array $validated): ReplaceWorkSchedulesData
    {
        return new ReplaceWorkSchedulesData(
            effectiveFrom: DateParser::parseLocalDate($validated['effective_from'], $user->timezone, 'effective_from'),
            schedules: collect($validated['schedules'])->map(
                fn (array $schedule) => new ReplaceWorkScheduleEntryData(
                    weekday: $schedule['weekday'],
                    type: WorkScheduleType::from($schedule['type']),
                    expectedMinutes: $schedule['expected_minutes'] ?? null,
                    startsAt: $schedule['starts_at'] ?? null,
                    endsAt: $schedule['ends_at'] ?? null,
                ),
            ),
        );
    }
}
