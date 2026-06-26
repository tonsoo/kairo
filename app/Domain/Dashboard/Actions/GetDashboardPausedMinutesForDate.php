<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\Shift;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class GetDashboardPausedMinutesForDate
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

        $rangeEnd = $referenceMoment->lt($dayStart->addDay())
            ? $referenceMoment
            : $dayStart->addDay();

        $segments = $shifts
            ->map(function (Shift $shift) use ($dayStart, $rangeEnd): array {
                $startedAt = $shift->started_at->toImmutable()->setTimezone($dayStart->timezone);
                $endedAt = $shift->ended_at === null
                    ? $rangeEnd
                    : $shift->ended_at->toImmutable()->setTimezone($dayStart->timezone);

                $overlapStart = $startedAt->greaterThan($dayStart) ? $startedAt : $dayStart;
                $overlapEnd = $endedAt->lessThan($rangeEnd) ? $endedAt : $rangeEnd;

                return [
                    'started_at' => $overlapStart,
                    'ended_at' => $overlapEnd,
                ];
            })
            ->filter(fn (array $segment) => $segment['ended_at']->gt($segment['started_at']))
            ->values();

        if ($segments->isEmpty()) {
            return 0;
        }

        /** @var array{started_at: CarbonImmutable, ended_at: CarbonImmutable} $firstSegment */
        $firstSegment = $segments->first();
        $workedMinutes = $segments->sum(
            fn (array $segment): int => (int) $segment['started_at']->diffInMinutes($segment['ended_at']),
        );
        $elapsedMinutes = (int) $firstSegment['started_at']->diffInMinutes($rangeEnd);

        return max($elapsedMinutes - $workedMinutes, 0);
    }
}
