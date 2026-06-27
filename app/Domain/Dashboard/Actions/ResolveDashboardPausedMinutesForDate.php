<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Shift\Actions\ResolveShiftOverlapPeriod;
use App\Domain\Shift\DTOs\ShiftOverlapPeriod;
use App\Models\Shift;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ResolveDashboardPausedMinutesForDate
{
    public function __construct(
        private ResolveShiftOverlapPeriod $resolveShiftOverlapPeriod,
    ) {}

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
            ->map(fn (Shift $shift) => ($this->resolveShiftOverlapPeriod)($shift, $dayStart, $rangeEnd))
            ->filter(fn (ShiftOverlapPeriod $segment) => $segment->end->gt($segment->start))
            ->values();

        if ($segments->isEmpty()) {
            return 0;
        }

        /** @var ShiftOverlapPeriod $firstSegment */
        $firstSegment = $segments->first();
        $workedMinutes = $segments->sum(
            fn (ShiftOverlapPeriod $segment): int => (int) $segment->start->diffInMinutes($segment->end),
        );
        $elapsedMinutes = (int) $firstSegment->start->diffInMinutes($rangeEnd);

        return max($elapsedMinutes - $workedMinutes, 0);
    }
}
