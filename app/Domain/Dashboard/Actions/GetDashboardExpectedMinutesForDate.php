<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class GetDashboardExpectedMinutesForDate
{
    /**
     * @param  Collection<int, Collection<int, WorkSchedule>>  $workSchedulesByWeekday
     */
    public function __invoke(Collection $workSchedulesByWeekday, CarbonImmutable $date): int
    {
        $weekdaySchedules = $workSchedulesByWeekday->get($date->dayOfWeekIso);

        if (! $weekdaySchedules instanceof Collection) {
            return 0;
        }

        /** @var WorkSchedule|null $workSchedule */
        $workSchedule = $weekdaySchedules
            ->filter(fn (WorkSchedule $workSchedule) => $workSchedule->effective_from->toImmutable()->lte($date->startOfDay()))
            ->last();

        return $workSchedule?->expected_minutes ?? 0;
    }
}
