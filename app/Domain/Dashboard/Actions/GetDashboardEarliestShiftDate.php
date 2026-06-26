<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions;

use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class GetDashboardEarliestShiftDate
{
    public function __invoke(User $user, string $timezone): ?CarbonImmutable
    {
        $shift = Shift::query()
            ->select('started_at')
            ->where('user_id', $user->id)
            ->orderBy('started_at')
            ->first();

        if ($shift === null) {
            return null;
        }

        return $shift->started_at
            ->toImmutable()
            ->setTimezone($timezone)
            ->startOfDay();
    }
}
