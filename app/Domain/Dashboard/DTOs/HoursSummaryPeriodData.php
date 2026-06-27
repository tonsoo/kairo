<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\DTOs;

use Carbon\CarbonImmutable;

final readonly class HoursSummaryPeriodData
{
    public function __construct(
        public CarbonImmutable $referenceDate,
        public CarbonImmutable $balanceStartsAt,
        public CarbonImmutable $monthStartsAt,
        public CarbonImmutable $monthEndsAt,
        public CarbonImmutable $semesterStartsAt,
        public CarbonImmutable $semesterEndsAt,
        public CarbonImmutable $periodStartsAt,
        public CarbonImmutable $periodEndsAt,
    ) {}
}
