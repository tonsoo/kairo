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
        private ResolveDashboardBalanceStartsAt $resolveDashboardBalanceStartsAt,
        private ListDashboardDailyDataForPeriod $listDashboardDailyDataForPeriod,
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
        $referenceDate = $referenceMoment->startOfDay();
        $currentMonthStart = $referenceMoment->startOfMonth();
        $currentSemesterStart = $currentMonthStart->subMonths(5);
        $balanceStartsAt = ($this->resolveDashboardBalanceStartsAt)(
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
        $periodStartsAt = $balanceStartsAt;
        $periodEndsAt = $referenceDate;

        if ($monthStart->lt($periodStartsAt)) {
            $periodStartsAt = $monthStart;
        }

        if ($semesterStart->lt($periodStartsAt)) {
            $periodStartsAt = $semesterStart;
        }

        if ($monthEndsAt->gt($periodEndsAt)) {
            $periodEndsAt = $monthEndsAt;
        }

        if ($semesterEndsAt->gt($periodEndsAt)) {
            $periodEndsAt = $semesterEndsAt;
        }

        $allDays = ($this->listDashboardDailyDataForPeriod)(
            $user,
            $periodStartsAt,
            $periodEndsAt,
            $referenceMoment,
        );
        $semesterDays = $allDays
            ->filter(fn (DashboardDayData $day) => $day->date->gte($semesterStart) && $day->date->lte($semesterEndsAt))
            ->values();
        $monthDays = $allDays
            ->filter(fn (DashboardDayData $day) => $day->date->gte($monthStart) && $day->date->lte($monthEndsAt))
            ->values();
        $balanceDays = $allDays
            ->filter(fn (DashboardDayData $day) => $day->date->gte($balanceStartsAt) && $day->date->lt($referenceDate))
            ->values();

        /** @var DashboardDayData|null $today */
        $today = $allDays->first(
            fn (DashboardDayData $day) => $day->date->equalTo($referenceDate),
        );

        if ($today === null) {
            throw new LogicException('Hours summary requires at least one day of data.');
        }

        $monthBalance = ($this->buildDashboardBalanceData)($monthDays);

        return new HoursSummaryData(
            generatedAt: $referenceMoment,
            timezone: $timezone,
            balance: ($this->buildDashboardBalanceData)($balanceDays),
            today: ($this->buildDashboardTodayData)($user, $today, $referenceMoment),
            semesterStartsAt: $semesterStart,
            semesterEndsAt: $semesterEndsAt,
            semesterItems: ($this->buildDashboardSemesterItems)($semesterDays),
            monthStartsAt: $monthStart,
            monthEndsAt: $monthEndsAt,
            monthBalanceMinutes: $monthBalance->balanceMinutes,
            monthItems: ($this->buildDashboardMonthItems)($monthDays),
        );
    }
}
