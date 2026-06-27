<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Dashboard\DTOs\DashboardDayData;
use App\Models\DailyWorkSchedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class BuildDashboardDaysForPeriod
{
    public function __construct(
        private ListDashboardRelevantShiftsForPeriod $listDashboardRelevantShiftsForPeriod,
        private ListDashboardRelevantDailyWorkSchedulesForPeriod $listDashboardRelevantDailyWorkSchedulesForPeriod,
        private ListDashboardRelevantWorkSchedulesForPeriod $listDashboardRelevantWorkSchedulesForPeriod,
        private ResolveDashboardExpectedMinutesForDate $getDashboardExpectedMinutesForDate,
        private ResolveDashboardWorkedMinutesForDate $getDashboardWorkedMinutesForDate,
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

        /** @var Collection<int, Shift> $shifts */
        $shifts = ($this->listDashboardRelevantShiftsForPeriod)($user, $periodStart, $periodEnd);

        /** @var Collection<string, DailyWorkSchedule> $dailyWorkSchedulesByDate */
        $dailyWorkSchedulesByDate = ($this->listDashboardRelevantDailyWorkSchedulesForPeriod)($user, $periodStart, $periodEnd)
            ->keyBy(fn (DailyWorkSchedule $schedule) => $schedule->date->toDateString());

        /** @var Collection<int, Collection<int, WorkSchedule>> $workSchedulesByWeekday */
        $workSchedulesByWeekday = ($this->listDashboardRelevantWorkSchedulesForPeriod)($user, $periodEnd);

        /** @var list<CarbonImmutable> $dates */
        $dates = iterator_to_array($periodStart->daysUntil($periodEnd->addDay()), false);

        return collect($dates)
            ->map(
                fn (CarbonImmutable $date) => $this->buildDayData(
                    shifts: $shifts,
                    dailyWorkSchedulesByDate: $dailyWorkSchedulesByDate,
                    workSchedulesByWeekday: $workSchedulesByWeekday,
                    date: $date,
                    referenceMoment: $referenceMoment,
                )
            )
            ->values();
    }

    /**
     * @param  Collection<int, Shift>  $shifts
     * @param  Collection<string, DailyWorkSchedule>  $dailyWorkSchedulesByDate
     * @param  Collection<int, Collection<int, WorkSchedule>>  $workSchedulesByWeekday
     */
    private function buildDayData(
        Collection $shifts,
        Collection $dailyWorkSchedulesByDate,
        Collection $workSchedulesByWeekday,
        CarbonImmutable $date,
        CarbonImmutable $referenceMoment,
    ): DashboardDayData {
        $workedMinutes = ($this->getDashboardWorkedMinutesForDate)(
            $shifts,
            $date,
            $referenceMoment,
        );

        $expectedMinutes = ($this->getDashboardExpectedMinutesForDate)(
            $dailyWorkSchedulesByDate,
            $workSchedulesByWeekday,
            $date,
        );

        return new DashboardDayData(
            date: $date,
            workedMinutes: $workedMinutes,
            expectedMinutes: $expectedMinutes,
            regularMinutes: min($workedMinutes, $expectedMinutes),
            extraMinutes: max($workedMinutes - $expectedMinutes, 0),
            missingMinutes: max($expectedMinutes - $workedMinutes, 0),
        );
    }
}
