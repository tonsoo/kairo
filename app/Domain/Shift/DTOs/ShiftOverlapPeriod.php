<?php

namespace App\Domain\Shift\DTOs;

use Carbon\CarbonImmutable;

final readonly class ShiftOverlapPeriod
{
    public function __construct(
        public CarbonImmutable $start,
        public CarbonImmutable $end,
    ) {}
}
