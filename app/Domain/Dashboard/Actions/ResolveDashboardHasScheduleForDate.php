<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\DailyWorkSchedule;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ResolveDashboardHasScheduleForDate
{
    /**
     * @param  Collection<string, DailyWorkSchedule>  $dailyWorkSchedulesByDate
     * @param  Collection<int, Collection<int, WorkSchedule>>  $workSchedulesByWeekday
     */
    public function __invoke(
        Collection $dailyWorkSchedulesByDate,
        Collection $workSchedulesByWeekday,
        CarbonImmutable $date,
    ): bool {
        $dailyWorkSchedule = $dailyWorkSchedulesByDate->get($date->toDateString());

        if ($dailyWorkSchedule instanceof DailyWorkSchedule) {
            return true;
        }

        /** @var Collection<int, WorkSchedule>|null $weekdaySchedules */
        $weekdaySchedules = $workSchedulesByWeekday->get($date->dayOfWeekIso);

        if ($weekdaySchedules === null) {
            return false;
        }

        return $weekdaySchedules->contains(
            fn (WorkSchedule $workSchedule): bool => $workSchedule->effective_from
                ->toImmutable()
                ->lte($date->startOfDay()),
        );
    }
}
