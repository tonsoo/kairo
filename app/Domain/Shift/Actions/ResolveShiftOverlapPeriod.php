<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftOverlapPeriod;
use App\Models\Shift;
use Carbon\CarbonImmutable;

final readonly class ResolveShiftOverlapPeriod
{
    public function __invoke(Shift $shift, CarbonImmutable $dayStart, CarbonImmutable $rangeEnd): ShiftOverlapPeriod
    {
        $startedAt = $shift->started_at->toImmutable()->setTimezone($dayStart->timezone);
        $endedAt = $shift->ended_at === null
            ? $rangeEnd
            : $shift->ended_at->toImmutable()->setTimezone($dayStart->timezone);

        $overlapStart = $startedAt->greaterThan($dayStart) ? $startedAt : $dayStart;
        $overlapEnd = $endedAt->lessThan($rangeEnd) ? $endedAt : $rangeEnd;

        return new ShiftOverlapPeriod(
            start: $overlapStart,
            end: $overlapEnd,
        );
    }
}
