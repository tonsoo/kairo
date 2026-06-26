<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Domain\Shift\Exceptions\NoOngoingShiftFound;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class EndShift
{
    public function __construct(
        private AssertShiftDoesNotOverlap $assertShiftDoesNotOverlap
    ) {}

    public function __invoke(User $user, CarbonImmutable $endedAt): Shift
    {
        $shift = Shift::query()
            ->where('user_id', $user->id)
            ->ongoing()
            ->latest('started_at')
            ->first();

        if ($shift === null) {
            throw NoOngoingShiftFound::forUser($user->id);
        }

        $period = new ShiftPeriodData(
            CarbonImmutable::instance($shift->started_at),
            $endedAt,
        );

        ($this->assertShiftDoesNotOverlap)($user, $period, $shift);

        $updatedShift = DB::transaction(function () use ($period, $shift) {
            $shift->forceFill([
                'ended_at' => $period->endedAt?->utc(),
            ])->save();

            return $shift;
        });

        return $updatedShift->fresh();
    }
}
