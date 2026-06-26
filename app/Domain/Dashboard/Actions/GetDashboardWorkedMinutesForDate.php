<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\Shift;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class GetDashboardWorkedMinutesForDate
{
    /**
     * @param  Collection<int, Shift>  $shifts
     */
    public function __invoke(Collection $shifts, CarbonImmutable $date, CarbonImmutable $referenceMoment): int
    {
        $dayStart = $date->startOfDay();

        if ($dayStart->gt($referenceMoment)) {
            return 0;
        }

        $dayEnd = $dayStart->addDay();
        $rangeEnd = $referenceMoment->lt($dayEnd) ? $referenceMoment : $dayEnd;

        return $shifts->sum(function (Shift $shift) use ($dayStart, $rangeEnd): int {
            $startedAt = $shift->started_at->toImmutable()->setTimezone($dayStart->timezone);
            $endedAt = $shift->ended_at === null
                ? $rangeEnd
                : $shift->ended_at->toImmutable()->setTimezone($dayStart->timezone);

            $overlapStart = $startedAt->greaterThan($dayStart) ? $startedAt : $dayStart;
            $overlapEnd = $endedAt->lessThan($rangeEnd) ? $endedAt : $rangeEnd;

            if ($overlapEnd->lte($overlapStart)) {
                return 0;
            }

            return (int) $overlapStart->diffInMinutes($overlapEnd);
        });
    }
}
