<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\DashboardDayData;
use App\Domain\Dashboard\DTOs\HoursSummaryDaysData;
use App\Domain\Dashboard\DTOs\HoursSummaryPeriodData;
use Illuminate\Support\Collection;
use LogicException;

final readonly class SliceHoursSummaryDays
{
    /**
     * @param  Collection<int, DashboardDayData>  $days
     */
    public function __invoke(Collection $days, HoursSummaryPeriodData $period): HoursSummaryDaysData
    {
        /** @var DashboardDayData|null $today */
        $today = $days->first(
            fn (DashboardDayData $day): bool => $day->date->equalTo($period->referenceDate),
        );

        if ($today === null) {
            throw new LogicException('Hours summary requires at least one day of data.');
        }

        return new HoursSummaryDaysData(
            today: $today,
            balanceDays: $days
                ->filter(fn (DashboardDayData $day): bool => $day->date->gte($period->balanceStartsAt)
                    && $day->date->lt($today->date))
                ->values(),
            semesterDays: $days
                ->filter(fn (DashboardDayData $day): bool => $day->date->gte($period->semesterStartsAt)
                    && $day->date->lte($period->semesterEndsAt)
                    && ! $day->date->equalTo($today->date))
                ->values(),
            monthBalanceDays: $days
                ->filter(fn (DashboardDayData $day): bool => $day->date->gte($period->monthStartsAt)
                    && $day->date->lte($period->monthEndsAt)
                    && ! $day->date->equalTo($today->date))
                ->values(),
            monthItems: $days
                ->filter(fn (DashboardDayData $day): bool => $day->date->gte($period->monthStartsAt)
                    && $day->date->lte($period->monthEndsAt))
                ->values(),
        );
    }
}
