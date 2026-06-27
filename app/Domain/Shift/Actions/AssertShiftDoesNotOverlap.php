<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Domain\Shift\Exceptions\ShiftOverlapDetected;
use App\Models\Shift;
use App\Models\User;

final readonly class AssertShiftDoesNotOverlap
{
    public function __invoke(User $user, ShiftPeriodData $period, Shift ...$ignoredShifts): void
    {
        $startedAt = $period->startedAt->utc();
        $endedAt = $period->endedAt?->utc();

        $ignoredShiftIds = collect($ignoredShifts)
            ->map(fn (Shift $shift) => $shift->getKey())
            ->all();

        $overlapExists = Shift::query()
            ->whereBelongsTo($user)
            ->when(
                $ignoredShiftIds !== [],
                fn ($query) => $query->whereKeyNot($ignoredShiftIds),
            )
            ->when(
                $endedAt !== null,
                fn ($query) => $query->where('started_at', '<', $endedAt),
            )
            ->where(function ($query) use ($startedAt): void {
                $query
                    ->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $startedAt);
            })
            ->exists();

        if ($overlapExists) {
            throw ShiftOverlapDetected::forUser($user->id);
        }
    }
}
