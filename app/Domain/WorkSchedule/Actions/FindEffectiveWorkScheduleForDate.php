<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;

final readonly class FindEffectiveWorkScheduleForDate
{
    public function __invoke(User $user, CarbonImmutable $date): ?WorkSchedule
    {
        $referenceDate = $date->startOfDay();

        return WorkSchedule::query()
            ->where('user_id', $user->id)
            ->where('weekday', $referenceDate->dayOfWeekIso)
            ->whereDate('effective_from', '<=', $referenceDate)
            ->orderByDesc('effective_from')
            ->first();
    }
}
