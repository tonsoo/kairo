<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\HoursSummaryPeriodData;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class ResolveHoursSummaryPeriod
{
    public function __construct(
        private ResolveDashboardBalanceStartsDate $resolveDashboardBalanceStartDate,
    ) {}

    public function __invoke(
        User $user,
        CarbonImmutable $referenceMoment,
        CarbonImmutable $monthStart,
        CarbonImmutable $semesterStart,
        string $timezone,
    ): HoursSummaryPeriodData {
        $referenceDate = $referenceMoment->startOfDay();
        $currentMonthStart = $referenceMoment->startOfMonth();
        $currentSemesterStart = $currentMonthStart->subMonths(5);

        $balanceStartsAt = ($this->resolveDashboardBalanceStartDate)(
            $user,
            $referenceMoment,
            $timezone,
        );

        $monthEndsAt = $monthStart->equalTo($currentMonthStart)
            ? $referenceDate
            : $monthStart->endOfMonth()->startOfDay();

        $semesterEndsAt = $semesterStart->equalTo($currentSemesterStart)
            ? $referenceDate
            : $semesterStart->addMonths(5)->endOfMonth()->startOfDay();

        return new HoursSummaryPeriodData(
            referenceDate: $referenceDate,
            balanceStartsAt: $balanceStartsAt,
            monthStartsAt: $monthStart,
            monthEndsAt: $monthEndsAt,
            semesterStartsAt: $semesterStart,
            semesterEndsAt: $semesterEndsAt,
            periodStartsAt: collect([$balanceStartsAt, $monthStart, $semesterStart])->min(),
            periodEndsAt: collect([$referenceDate, $monthEndsAt, $semesterEndsAt])->max(),
        );
    }
}
