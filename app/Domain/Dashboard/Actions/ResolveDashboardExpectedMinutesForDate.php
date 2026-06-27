<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\DailyWorkSchedule;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ResolveDashboardExpectedMinutesForDate
{
    /**
     * @param  Collection<string, DailyWorkSchedule>  $dailyWorkSchedulesByDate
     * @param  Collection<int, Collection<int, WorkSchedule>>  $workSchedulesByWeekday
     */
    public function __invoke(
        Collection $dailyWorkSchedulesByDate,
        Collection $workSchedulesByWeekday,
        CarbonImmutable $date,
    ): int {
        $dailyWorkSchedule = $dailyWorkSchedulesByDate->get($date->toDateString());

        if ($dailyWorkSchedule instanceof DailyWorkSchedule) {
            return $dailyWorkSchedule->expected_minutes;
        }

        /** @var Collection<int, WorkSchedule>|null $weekdaySchedules */
        $weekdaySchedules = $workSchedulesByWeekday->get($date->dayOfWeekIso);

        return $weekdaySchedules
            ? $this->expectedMinutesFromWorkSchedules($weekdaySchedules, $date)
            : 0;
    }

    /**
     * @param  Collection<int, WorkSchedule>  $workSchedules
     */
    private function expectedMinutesFromWorkSchedules(Collection $workSchedules, CarbonImmutable $date): int
    {
        /** @var WorkSchedule|null $workSchedule */
        $workSchedule = $workSchedules->last(
            fn (WorkSchedule $workSchedule): bool => $workSchedule->effective_from
                ->toImmutable()
                ->lte($date->startOfDay()),
        );

        return $workSchedule?->expected_minutes ?? 0;
    }
}
