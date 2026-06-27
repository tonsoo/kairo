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
        $days = ($this->listDashboardDailyDataForPeriod)(
            $user,
            $startsAt,
            $endsAt,
            $referenceMoment,
        )
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
            workedMinutes: $days->sum(
                fn (ShiftExportDayData $day): int => $day->workedMinutes,
            ),
            regularMinutes: $days->sum(
                fn (ShiftExportDayData $day): int => $day->regularMinutes,
            ),
            extraMinutes: $days->sum(
                fn (ShiftExportDayData $day): int => $day->extraMinutes,
            ),
            missingMinutes: $days->sum(
                fn (ShiftExportDayData $day): int => $day->missingMinutes,
            ),
            days: $days,
        );
    }
}
