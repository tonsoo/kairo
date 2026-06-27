<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\DailyWorkSchedule;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class GetDashboardExpectedMinutesForDate
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

        $weekdaySchedules = $workSchedulesByWeekday->get($date->dayOfWeekIso);

        if (! $weekdaySchedules instanceof Collection) {
            return 0;
        }

        /** @var WorkSchedule|null $workSchedule */
        $workSchedule = $weekdaySchedules
            ->last(
                fn (WorkSchedule $workSchedule) => $workSchedule->effective_from->toImmutable()->lte($date->startOfDay())
            );

        if (! $workSchedule instanceof WorkSchedule) {
            return 0;
        }

        return $workSchedule->expected_minutes;
    }
}
