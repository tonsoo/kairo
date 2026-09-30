<?php

namespace App\Domain\Holidays\DTOs;

use Carbon\CarbonImmutable;

final readonly class HolidayData
{
    public function __construct(
        public string $name,
        public CarbonImmutable $date,
        public bool $isNational,
    ) {}
}
