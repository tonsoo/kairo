<?php

namespace App\Domain\Shift\DTOs;

use App\Domain\Shift\Enums\CurrentShiftAction;
use App\Models\Shift;
use Carbon\CarbonImmutable;

final readonly class CurrentShiftStateData
{
    public function __construct(
        public CurrentShiftAction $nextAction,
        public CarbonImmutable $localDate,
        public bool $hasShiftToday = false,
        public bool $hasOngoingShift = false,
        public ?Shift $activeShift = null,
        public ?Shift $latestShift = null,
    ) {}
}
