<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\DTOs;

final readonly class DashboardBalanceData
{
    public function __construct(
        public int $balanceMinutes,
        public int $positiveMinutes,
        public int $negativeMinutes,
    ) {}
}
