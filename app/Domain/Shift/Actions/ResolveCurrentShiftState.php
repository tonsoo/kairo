<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\CurrentShiftStateData;
use App\Domain\Shift\Enums\CurrentShiftAction;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class ResolveCurrentShiftState
{
    public function __invoke(User $user, CarbonImmutable $moment): CurrentShiftStateData
    {
        $dayStart = $moment->startOfDay();
        $dayEnd = $dayStart->addDay();

        $matchingShifts = Shift::query()
            ->where('user_id', $user->id)
            ->where('started_at', '<', $dayEnd->utc())
            ->where(function ($query) use ($dayStart) {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $dayStart->utc());
            });

        $activeShift = (clone $matchingShifts)
            ->ongoing()
            ->latest('started_at')
            ->first();

        if ($activeShift !== null) {
            return new CurrentShiftStateData(
                nextAction: CurrentShiftAction::end,
                localDate: $dayStart,
                hasShiftToday: true,
                hasOngoingShift: true,
                activeShift: $activeShift,
                latestShift: $activeShift,
            );
        }

        $latestShift = (clone $matchingShifts)
            ->completed()
            ->latest('started_at')
            ->first();

        if ($latestShift !== null) {
            return new CurrentShiftStateData(
                nextAction: CurrentShiftAction::continue,
                localDate: $dayStart,
                hasShiftToday: true,
                latestShift: $latestShift,
            );
        }

        return new CurrentShiftStateData(
            nextAction: CurrentShiftAction::start,
            localDate: $dayStart,
        );
    }
}
