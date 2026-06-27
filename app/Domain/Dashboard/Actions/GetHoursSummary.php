<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\HoursSummaryData;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class GetHoursSummary
{
    public function __construct(
        private ResolveHoursSummaryPeriod $resolveHoursSummaryPeriod,
        private BuildDashboardDaysForPeriod $buildDashboardDaysForPeriod,
        private SliceHoursSummaryDays $sliceHoursSummaryDays,
        private BuildDashboardBalanceData $buildDashboardBalanceData,
        private BuildDashboardTodayData $buildDashboardTodayData,
        private BuildDashboardSemesterItems $buildDashboardSemesterItems,
        private BuildDashboardMonthItems $buildDashboardMonthItems,
    ) {}

    public function __invoke(
        User $user,
        CarbonImmutable $referenceMoment,
        CarbonImmutable $monthStart,
        CarbonImmutable $semesterStart,
        string $timezone,
    ): HoursSummaryData {
        $period = ($this->resolveHoursSummaryPeriod)(
            $user,
            $referenceMoment,
            $monthStart,
            $semesterStart,
            $timezone,
        );

        $allDays = ($this->buildDashboardDaysForPeriod)(
            $user,
            $period->periodStartsAt,
            $period->periodEndsAt,
            $referenceMoment,
        );

        $days = ($this->sliceHoursSummaryDays)($allDays, $period);

        $monthBalance = ($this->buildDashboardBalanceData)($days->monthBalanceDays);

        return new HoursSummaryData(
            generatedAt: $referenceMoment,
            timezone: $timezone,
            balance: ($this->buildDashboardBalanceData)($days->balanceDays),
            today: ($this->buildDashboardTodayData)($user, $days->today, $referenceMoment),
            semesterStartsAt: $period->semesterStartsAt,
            semesterEndsAt: $period->semesterEndsAt,
            semesterItems: ($this->buildDashboardSemesterItems)($days->semesterDays),
            monthStartsAt: $period->monthStartsAt,
            monthEndsAt: $period->monthEndsAt,
            monthBalanceMinutes: $monthBalance->balanceMinutes,
            monthItems: ($this->buildDashboardMonthItems)($days->monthItems),
        );
    }
}
