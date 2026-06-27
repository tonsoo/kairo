<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Domain\Shift\Actions\ResolveShiftOverlapPeriod;
use App\Models\Shift;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ResolveDashboardWorkedMinutesForDate
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

        $dayEnd = $dayStart->addDay();
        $rangeEnd = $referenceMoment->lt($dayEnd) ? $referenceMoment : $dayEnd;

        return $shifts->sum(function (Shift $shift) use ($dayStart, $rangeEnd): int {
            $overlap = ($this->resolveShiftOverlapPeriod)($shift, $dayStart, $rangeEnd);

            if ($overlap->end->lte($overlap->start)) {
                return 0;
            }

            return (int) $overlap->start->diffInMinutes($overlap->end);
        });
    }
}
