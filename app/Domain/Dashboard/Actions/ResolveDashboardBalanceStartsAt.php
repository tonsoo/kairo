<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class ResolveDashboardBalanceStartsAt
{
    public function __construct(
        private GetDashboardEarliestShiftDate $getDashboardEarliestShiftDate,
        private GetDashboardEarliestWorkScheduleDate $getDashboardEarliestWorkScheduleDate,
    ) {}

    public function __invoke(User $user, CarbonImmutable $referenceMoment): CarbonImmutable
    {
        $referenceDate = $referenceMoment->startOfDay();
        $earliestShiftDate = ($this->getDashboardEarliestShiftDate)(
            $user,
            $user->timezone,
        );
        $earliestWorkScheduleDate = ($this->getDashboardEarliestWorkScheduleDate)(
            $user,
            $user->timezone,
        );
        $startsAt = $referenceDate;

        if ($earliestShiftDate !== null && $earliestShiftDate->lt($startsAt)) {
            $startsAt = $earliestShiftDate;
        }

        if ($earliestWorkScheduleDate !== null && $earliestWorkScheduleDate->lt($startsAt)) {
            $startsAt = $earliestWorkScheduleDate;
        }

        return $startsAt;
    }
}
