<?php

declare(strict_types=1);

namespace App\Domain\Shift\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ShiftExportData
{
    /**
     * @param  Collection<int, ShiftExportDayData>  $days
     */
    public function __construct(
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public string $timezone,
        public int $workedMinutes,
        public int $regularMinutes,
        public int $extraMinutes,
        public int $missingMinutes,
        public Collection $days,
    ) {}
}
