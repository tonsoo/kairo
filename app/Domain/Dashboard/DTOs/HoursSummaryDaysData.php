<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\DTOs;

use Illuminate\Support\Collection;

final readonly class HoursSummaryDaysData
{
    /**
     * @param  Collection<int, DashboardDayData>  $balanceDays
     * @param  Collection<int, DashboardDayData>  $semesterDays
     * @param  Collection<int, DashboardDayData>  $monthBalanceDays
     * @param  Collection<int, DashboardDayData>  $monthItems
     */
    public function __construct(
        public DashboardDayData $today,
        public Collection $balanceDays,
        public Collection $semesterDays,
        public Collection $monthBalanceDays,
        public Collection $monthItems,
    ) {}
}
