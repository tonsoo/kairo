<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\DashboardDayData;
use App\Domain\Dashboard\DTOs\HoursSummaryData;
use App\Models\User;
use Carbon\CarbonImmutable;
use LogicException;

final readonly class GetHoursSummary
{
    public function __construct(
        private ListDashboardDailyDataForPeriod $listDashboardDailyDataForPeriod,
        private BuildDashboardBalanceData $buildDashboardBalanceData,
        private BuildDashboardTodayData $buildDashboardTodayData,
        private BuildDashboardSemesterItems $buildDashboardSemesterItems,
        private BuildDashboardMonthItems $buildDashboardMonthItems,
    ) {}

    public function __invoke(User $user, CarbonImmutable $referenceMoment): HoursSummaryData
    {
        $monthEndsAt = $referenceMoment->startOfDay();
        $monthStartsAt = $referenceMoment->startOfMonth();
        $semesterStartsAt = $referenceMoment->startOfMonth()->subMonths(5);
        $semesterDays = ($this->listDashboardDailyDataForPeriod)(
            $user,
            $semesterStartsAt,
            $monthEndsAt,
            $referenceMoment,
        );

        /** @var DashboardDayData|null $today */
        $today = $semesterDays->last();

        if ($today === null) {
            throw new LogicException('Hours summary requires at least one day of data.');
        }

        $monthDays = $semesterDays
            ->filter(fn (DashboardDayData $day) => $day->date->gte($monthStartsAt))
            ->values();
        $monthBalance = ($this->buildDashboardBalanceData)($monthDays);

        return new HoursSummaryData(
            generatedAt: $referenceMoment,
            timezone: $user->timezone,
            balance: ($this->buildDashboardBalanceData)($semesterDays),
            today: ($this->buildDashboardTodayData)($user, $today, $referenceMoment),
            semesterStartsAt: $semesterStartsAt,
            semesterEndsAt: $monthEndsAt,
            semesterItems: ($this->buildDashboardSemesterItems)($semesterDays),
            monthStartsAt: $monthStartsAt,
            monthEndsAt: $monthEndsAt,
            monthBalanceMinutes: $monthBalance->balanceMinutes,
            monthItems: ($this->buildDashboardMonthItems)($monthDays),
        );
    }
}
