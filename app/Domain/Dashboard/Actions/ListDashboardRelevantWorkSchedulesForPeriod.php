<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ListDashboardRelevantWorkSchedulesForPeriod
{
    /**
     * @return Collection<int, Collection<int, WorkSchedule>>
     */
    public function __invoke(User $user, CarbonImmutable $endsAt): Collection
    {
        return WorkSchedule::query()
            ->where('user_id', $user->id)
            ->whereDate('effective_from', '<=', $endsAt->startOfDay())
            ->orderBy('effective_from')
            ->get()
            ->groupBy('weekday')
            ->map(fn (Collection $workSchedules) => $workSchedules->values());
    }
}
