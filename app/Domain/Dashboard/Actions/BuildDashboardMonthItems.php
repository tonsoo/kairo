<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\DashboardDayData;
use App\Domain\Dashboard\DTOs\DashboardPeriodItemData;
use Illuminate\Support\Collection;

final readonly class BuildDashboardMonthItems
{
    /**
     * @param  Collection<int, DashboardDayData>  $days
     * @return Collection<int, DashboardPeriodItemData>
     */
    public function __invoke(Collection $days): Collection
    {
        return $days
            ->map(
                fn (DashboardDayData $day) => new DashboardPeriodItemData(
                    date: $day->date,
                    workedMinutes: $day->workedMinutes,
                    regularMinutes: $day->regularMinutes,
                    extraMinutes: $day->extraMinutes,
                    missingMinutes: $day->missingMinutes,
                )
            )
            ->values();
    }
}
