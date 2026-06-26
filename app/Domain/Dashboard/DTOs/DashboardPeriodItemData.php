<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\DTOs;

use Carbon\CarbonImmutable;

final readonly class DashboardPeriodItemData
{
    public function __construct(
        public CarbonImmutable $date,
        public int $workedMinutes,
        public int $regularMinutes,
        public int $extraMinutes,
        public int $missingMinutes,
    ) {}
}
