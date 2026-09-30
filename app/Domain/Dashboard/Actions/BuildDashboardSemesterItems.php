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
                $workedMinutes = $monthDays->sum(fn (DashboardDayData $day): int => $day->workedMinutes);
                $expectedMinutes = $monthDays->sum(fn (DashboardDayData $day): int => $day->expectedMinutes);

                return new DashboardPeriodItemData(
                    date: $firstDay->date->startOfMonth(),
                    hasSchedule: $monthDays->contains(
                        fn (DashboardDayData $day): bool => $day->hasSchedule,
                    ),
                    workedMinutes: $workedMinutes,
                    expectedMinutes: $expectedMinutes,
                    regularMinutes: min($workedMinutes, $expectedMinutes),
                    extraMinutes: max($workedMinutes - $expectedMinutes, 0),
                    missingMinutes: max($expectedMinutes - $workedMinutes, 0),
                );
            })
            ->values()
            ->collect();
    }
}
