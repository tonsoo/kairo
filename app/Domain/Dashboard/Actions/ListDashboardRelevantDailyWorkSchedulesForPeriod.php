<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\DailyWorkSchedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ListDashboardRelevantDailyWorkSchedulesForPeriod
{
    /**
     * @return Collection<int, DailyWorkSchedule>
     */
    public function __invoke(User $user, CarbonImmutable $startsAt, CarbonImmutable $endsAt): Collection
    {
        return DailyWorkSchedule::query()
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', $startsAt->startOfDay())
            ->whereDate('date', '<=', $endsAt->startOfDay())
            ->orderBy('date')
            ->get();
    }
}
