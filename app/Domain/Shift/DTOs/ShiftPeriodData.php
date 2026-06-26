<?php

declare(strict_types=1);

namespace App\Domain\Shift\DTOs;

use App\Domain\Shift\Exceptions\InvalidShiftPeriod;
use Carbon\CarbonImmutable;

final readonly class ShiftPeriodData
{
    public function __construct(
        public CarbonImmutable $startedAt,
        public ?CarbonImmutable $endedAt,
    ) {
        if ($this->endedAt !== null && $this->endedAt->lessThanOrEqualTo($this->startedAt)) {
            throw InvalidShiftPeriod::chronology();
        }
    }
}
