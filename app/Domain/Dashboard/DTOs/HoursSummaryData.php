<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class HoursSummaryData
{
    /**
     * @param  Collection<int, DashboardPeriodItemData>  $semesterItems
     * @param  Collection<int, DashboardPeriodItemData>  $monthItems
     */
    public function __construct(
        public CarbonImmutable $generatedAt,
        public string $timezone,
        public DashboardBalanceData $balance,
        public DashboardTodayData $today,
        public CarbonImmutable $semesterStartsAt,
        public CarbonImmutable $semesterEndsAt,
        public Collection $semesterItems,
        public CarbonImmutable $monthStartsAt,
        public CarbonImmutable $monthEndsAt,
        public int $monthBalanceMinutes,
        public Collection $monthItems,
    ) {}
}
