<?php

declare(strict_types=1);

namespace App\Domain\Shift\DTOs;

use Carbon\CarbonImmutable;

final readonly class ShiftExportDayData
{
    public function __construct(
        public CarbonImmutable $date,
        public int $workedMinutes,
        public int $expectedMinutes,
        public int $regularMinutes,
        public int $extraMinutes,
        public int $missingMinutes,
    ) {}
}
