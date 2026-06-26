<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Domain\Shift\Exceptions\ShiftOverlapDetected;
use App\Models\Shift;
use App\Models\User;

final readonly class AssertShiftDoesNotOverlap
{
    public function __invoke(User $user, ShiftPeriodData $period, ?Shift $ignoredShift = null): void
    {
        $startedAt = $period->startedAt->utc();
        $endedAt = $period->endedAt?->utc();

        $overlapExists = Shift::query()
            ->where('user_id', $user->id)
            ->when(
                $ignoredShift !== null,
                fn ($query) => $query->whereKeyNot($ignoredShift->getKey())
            )
            ->when(
                $endedAt !== null,
                fn ($query) => $query->where('started_at', '<', $endedAt)
            )
            ->where(function ($query) use ($startedAt) {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $startedAt);
            })
            ->exists();

        if ($overlapExists) {
            throw ShiftOverlapDetected::forUser($user->id);
        }
    }
}
