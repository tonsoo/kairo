<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class RemoveShiftBreak
{
    public function __construct(
        private AssertShiftBreakCanBeRemoved $assertShiftBreakCanBeRemoved,
        private AssertShiftDoesNotOverlap $assertShiftDoesNotOverlap,
    ) {}

    public function __invoke(User $user, Shift $previousShift, Shift $nextShift): Shift
    {
        return DB::transaction(function () use ($user, $previousShift, $nextShift): Shift {
            $previousShift = Shift::query()
                ->whereKey($previousShift->id)
                ->lockForUpdate()
                ->firstOrFail();

            $nextShift = Shift::query()
                ->whereKey($nextShift->id)
                ->lockForUpdate()
                ->firstOrFail();

            ($this->assertShiftBreakCanBeRemoved)($user, $previousShift, $nextShift);

            $mergedPeriod = new ShiftPeriodData(
                startedAt: CarbonImmutable::instance($previousShift->started_at),
                endedAt: $nextShift->ended_at === null
                    ? null
                    : CarbonImmutable::instance($nextShift->ended_at),
            );

            ($this->assertShiftDoesNotOverlap)(
                $user,
                $mergedPeriod,
                $previousShift,
                $nextShift,
            );

            $previousShift->forceFill([
                'ended_at' => $nextShift->ended_at,
            ])->save();

            $nextShift->delete();

            return $previousShift->fresh();
        });
    }
}
