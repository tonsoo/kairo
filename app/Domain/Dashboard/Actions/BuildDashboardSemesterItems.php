<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\DashboardDayData;
use App\Domain\Dashboard\DTOs\DashboardPeriodItemData;
use Illuminate\Support\Collection;

final readonly class BuildDashboardSemesterItems
{
    /**
     * @param  Collection<int, DashboardDayData>  $days
     * @return Collection<int, DashboardPeriodItemData>
     */
    public function __invoke(Collection $days): Collection
    {
        return $days
            ->groupBy(fn (DashboardDayData $day) => $day->date->startOfMonth()->toDateString())
            ->map(function (Collection $monthDays) {
                /** @var DashboardDayData $firstDay */
                $firstDay = $monthDays->first();

                return new DashboardPeriodItemData(
                    date: $firstDay->date->startOfMonth(),
                    hasSchedule: $monthDays->contains(
                        fn (DashboardDayData $day): bool => $day->hasSchedule,
                    ),
                    workedMinutes: $monthDays->sum(
                        fn (DashboardDayData $day): int => $day->workedMinutes,
                    ),
                    expectedMinutes: $monthDays->sum(
                        fn (DashboardDayData $day): int => $day->expectedMinutes,
                    ),
                    regularMinutes: $monthDays->sum(
                        fn (DashboardDayData $day): int => $day->regularMinutes,
                    ),
                    extraMinutes: $monthDays->sum(
                        fn (DashboardDayData $day): int => $day->extraMinutes,
                    ),
                    missingMinutes: $monthDays->sum(
                        fn (DashboardDayData $day): int => $day->missingMinutes,
                    ),
                );
            })
            ->values()
            ->collect();
    }
}
