<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;

final readonly class GetDashboardEarliestWorkScheduleDate
{
    public function __invoke(User $user, string $timezone): ?CarbonImmutable
    {
        $workSchedule = WorkSchedule::query()
            ->select('effective_from')
            ->where('user_id', $user->id)
            ->orderBy('effective_from')
            ->first();

        if ($workSchedule === null) {
            return null;
        }

        return CarbonImmutable::instance($workSchedule->effective_from)
            ->setTimezone($timezone)
            ->startOfDay();
    }
}
