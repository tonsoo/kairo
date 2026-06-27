<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\DashboardDayData;
use App\Domain\Dashboard\DTOs\DashboardTodayData;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class BuildDashboardTodayData
{
    public function __construct(
        private ListDashboardRelevantShiftsForPeriod $listDashboardRelevantShiftsForPeriod,
        private ResolveDashboardPausedMinutesForDate $getDashboardPausedMinutesForDate,
    ) {}

    public function __invoke(User $user, DashboardDayData $day, CarbonImmutable $referenceMoment): DashboardTodayData
    {
        $shifts = ($this->listDashboardRelevantShiftsForPeriod)($user, $day->date, $day->date);
        $pausedMinutes = ($this->getDashboardPausedMinutesForDate)($shifts, $day->date, $referenceMoment);

        return new DashboardTodayData(
            date: $day->date,
            workedMinutes: $day->workedMinutes,
            pausedMinutes: $pausedMinutes,
            expectedMinutes: $day->expectedMinutes,
            regularMinutes: $day->regularMinutes,
            extraMinutes: $day->extraMinutes,
            missingMinutes: $day->missingMinutes,
        );
    }
}
