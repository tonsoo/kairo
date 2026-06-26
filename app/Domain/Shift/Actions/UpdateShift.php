<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class UpdateShift
{
    public function __construct(
        private AssertShiftBelongsToUser $assertShiftBelongsToUser,
        private AssertShiftDoesNotOverlap $assertShiftDoesNotOverlap,
    ) {}

    public function __invoke(User $user, Shift $shift, ShiftPeriodData $period): Shift
    {
        ($this->assertShiftBelongsToUser)($user, $shift);
        ($this->assertShiftDoesNotOverlap)($user, $period, $shift);

        $updatedShift = DB::transaction(function () use ($period, $shift) {
            $shift->forceFill([
                'started_at' => $period->startedAt->utc(),
                'ended_at' => $period->endedAt?->utc(),
            ])->save();

            return $shift;
        });

        return $updatedShift->fresh();
    }
}
