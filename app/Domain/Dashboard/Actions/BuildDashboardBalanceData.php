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
        $balanceMinutes = $days->sum(
            fn (DashboardDayData $day): int => $day->workedMinutes - $day->expectedMinutes,
        );

        return new DashboardBalanceData(
            balanceMinutes: $balanceMinutes,
            positiveMinutes: max($balanceMinutes, 0),
            negativeMinutes: max(-$balanceMinutes, 0),
        );
    }
}
