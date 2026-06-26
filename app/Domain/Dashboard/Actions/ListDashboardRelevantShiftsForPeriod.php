<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class ListDashboardRelevantShiftsForPeriod
{
    /**
     * @return Collection<int, Shift>
     */
    public function __invoke(User $user, CarbonImmutable $startsAt, CarbonImmutable $endsAt): Collection
    {
        $periodStart = $startsAt->startOfDay();
        $periodEnd = $endsAt->startOfDay()->addDay();

        return Shift::query()
            ->where('user_id', $user->id)
            ->where('started_at', '<', $periodEnd->utc())
            ->where(function (Builder $query) use ($periodStart): void {
                $query
                    ->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $periodStart->utc());
            })
            ->orderBy('started_at')
            ->get();
    }
}
