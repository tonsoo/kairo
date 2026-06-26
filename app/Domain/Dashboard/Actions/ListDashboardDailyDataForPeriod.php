<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\DashboardDayData;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ListDashboardDailyDataForPeriod
{
    public function __construct(
        private ListDashboardRelevantShiftsForPeriod $listDashboardRelevantShiftsForPeriod,
        private ListDashboardRelevantDailyWorkSchedulesForPeriod $listDashboardRelevantDailyWorkSchedulesForPeriod,
        private ListDashboardRelevantWorkSchedulesForPeriod $listDashboardRelevantWorkSchedulesForPeriod,
        private GetDashboardExpectedMinutesForDate $getDashboardExpectedMinutesForDate,
        private GetDashboardWorkedMinutesForDate $getDashboardWorkedMinutesForDate,
    ) {}

    /**
     * @return Collection<int, DashboardDayData>
     */
    public function __invoke(
        User $user,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        CarbonImmutable $referenceMoment,
    ): Collection {
        $periodStart = $startsAt->startOfDay();
        $periodEnd = $endsAt->startOfDay();
        $shifts = ($this->listDashboardRelevantShiftsForPeriod)($user, $periodStart, $periodEnd);
        $dailyWorkSchedulesByDate = ($this->listDashboardRelevantDailyWorkSchedulesForPeriod)($user, $periodStart, $periodEnd)
            ->keyBy(fn ($dailyWorkSchedule) => $dailyWorkSchedule->date->toDateString());
        $workSchedulesByWeekday = ($this->listDashboardRelevantWorkSchedulesForPeriod)($user, $periodEnd);
        $days = collect();

        for ($date = $periodStart; $date->lte($periodEnd); $date = $date->addDay()) {
            $workedMinutes = ($this->getDashboardWorkedMinutesForDate)($shifts, $date, $referenceMoment);
            $expectedMinutes = ($this->getDashboardExpectedMinutesForDate)(
                $dailyWorkSchedulesByDate,
                $workSchedulesByWeekday,
                $date,
            );
            $regularMinutes = min($workedMinutes, $expectedMinutes);
            $extraMinutes = max($workedMinutes - $expectedMinutes, 0);
            $missingMinutes = max($expectedMinutes - $workedMinutes, 0);

            $days->push(new DashboardDayData(
                date: $date,
                workedMinutes: $workedMinutes,
                expectedMinutes: $expectedMinutes,
                regularMinutes: $regularMinutes,
                extraMinutes: $extraMinutes,
                missingMinutes: $missingMinutes,
            ));
        }

        return $days;
    }
}
