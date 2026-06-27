<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class ResolveDashboardBalanceStartsDate
{
    public function __construct(
        private ResolveDashboardEarliestShiftDate        $getDashboardEarliestShiftDate,
        private ResolveDashboardEarliestWorkScheduleDate $getDashboardEarliestWorkScheduleDate,
    ) {}

    public function __invoke(User $user, CarbonImmutable $referenceMoment, string $timezone): CarbonImmutable
    {
        $referenceDate = $referenceMoment->startOfDay();
        $earliestShiftDate = ($this->getDashboardEarliestShiftDate)(
            $user,
            $timezone,
        );
        $earliestWorkScheduleDate = ($this->getDashboardEarliestWorkScheduleDate)(
            $user,
            $timezone,
        );

        return collect([
            $referenceDate,
            $earliestShiftDate,
            $earliestWorkScheduleDate,
        ])
            ->filter()
            ->min();
    }
}
