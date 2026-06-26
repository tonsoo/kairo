<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\Exceptions\ShiftOverlapDetected;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class RemoveShiftBreak
{
    public function __construct(
        private AssertShiftBreakCanBeRemoved $assertShiftBreakCanBeRemoved,
    ) {}

    public function __invoke(User $user, Shift $previousShift, Shift $nextShift): Shift
    {
        ($this->assertShiftBreakCanBeRemoved)($user, $previousShift, $nextShift);

        $mergedStartedAt = $previousShift->started_at->clone();
        $mergedEndedAt = $nextShift->ended_at?->clone();

        $overlapExists = Shift::query()
            ->where('user_id', $user->id)
            ->whereNotIn('id', [$previousShift->id, $nextShift->id])
            ->when(
                $mergedEndedAt !== null,
                fn ($query) => $query->where('started_at', '<', $mergedEndedAt->utc()),
            )
            ->where(function ($query) use ($mergedStartedAt): void {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $mergedStartedAt->utc());
            })
            ->exists();

        if ($overlapExists) {
            throw ShiftOverlapDetected::forUser($user->id);
        }

        $mergedShift = DB::transaction(function () use ($previousShift, $nextShift) {
            $previousShift->forceFill([
                'ended_at' => $nextShift->ended_at,
            ])->save();

            $nextShift->delete();

            return $previousShift;
        });

        return $mergedShift->fresh();
    }
}
