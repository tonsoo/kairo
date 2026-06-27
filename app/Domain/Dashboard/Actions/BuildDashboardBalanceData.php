<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\DashboardBalanceData;
use App\Domain\Dashboard\DTOs\DashboardDayData;
use Illuminate\Support\Collection;

final readonly class BuildDashboardBalanceData
{
    /**
     * @param  Collection<int, DashboardDayData>  $days
     */
    public function __invoke(Collection $days): DashboardBalanceData
    {
        $positiveMinutes = $days->sum(
            fn (DashboardDayData $day) => $day->extraMinutes,
        );
        $negativeMinutes = $days->sum(
            fn (DashboardDayData $day) => $day->missingMinutes,
        );

        return new DashboardBalanceData(
            balanceMinutes: $positiveMinutes - $negativeMinutes,
            positiveMinutes: $positiveMinutes,
            negativeMinutes: $negativeMinutes,
        );
    }
}
