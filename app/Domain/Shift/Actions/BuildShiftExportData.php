<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Dashboard\Actions\BuildDashboardDaysForPeriod;
use App\Domain\Dashboard\DTOs\DashboardDayData;
use App\Domain\Shift\DTOs\ShiftExportData;
use App\Domain\Shift\DTOs\ShiftExportDayData;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class BuildShiftExportData
{
    public function __construct(
        private BuildDashboardDaysForPeriod $listDashboardDailyDataForPeriod,
    ) {}

    public function __invoke(
        User $user,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        CarbonImmutable $referenceMoment,
        string $timezone,
    ): ShiftExportData {
        $allDays = ($this->listDashboardDailyDataForPeriod)(
            $user,
            $startsAt,
            $endsAt,
            $referenceMoment,
        );

        $workedMinutes = $allDays->sum(fn (DashboardDayData $day): int => $day->workedMinutes);
        $expectedMinutes = $allDays->sum(fn (DashboardDayData $day): int => $day->expectedMinutes);

        $days = $allDays
            ->filter(
                fn (DashboardDayData $day) => $day->workedMinutes > 0,
            )
            ->map(fn (DashboardDayData $day) => new ShiftExportDayData(
                date: $day->date,
                workedMinutes: $day->workedMinutes,
                expectedMinutes: $day->expectedMinutes,
                regularMinutes: $day->regularMinutes,
                extraMinutes: $day->extraMinutes,
                missingMinutes: $day->missingMinutes,
            ))
            ->values();

        return new ShiftExportData(
            startsAt: $startsAt,
            endsAt: $endsAt,
            timezone: $timezone,
            workedMinutes: $workedMinutes,
            regularMinutes: min($workedMinutes, $expectedMinutes),
            extraMinutes: max($workedMinutes - $expectedMinutes, 0),
            missingMinutes: max($expectedMinutes - $workedMinutes, 0),
            days: $days,
        );
    }
}
