<?php

declare(strict_types=1);

namespace App\Domain\Shift\DTOs;

use App\Support\Formatting\TimeFormatter;
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

    public function weekdayLabel(): string
    {
        /** @var CarbonImmutable $localizedDate */
        $localizedDate = CarbonImmutable::instance($this->date)->locale(app()->getLocale());

        return mb_strtoupper($localizedDate->isoFormat('ddd'));
    }

    public function dateAsDM(): string
    {
        return $this->date->format('d/m');
    }

    public function durationAsReadableHours(): string
    {
        return TimeFormatter::formatMinutesToReadableHours($this->workedMinutes);
    }
}
