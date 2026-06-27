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
        return DB::transaction(function () use ($user, $endedAt): Shift {
            $shift = Shift::query()
                ->whereBelongsTo($user)
                ->ongoing()
                ->latest('started_at')
                ->lockForUpdate()
                ->first();

            if ($shift === null) {
                throw NoOngoingShiftFound::forUser($user->id);
            }

            $period = new ShiftPeriodData(
                startedAt: CarbonImmutable::instance($shift->started_at),
                endedAt: $endedAt,
            );

            ($this->assertShiftDoesNotOverlap)($user, $period, $shift);

            $shift->forceFill([
                'ended_at' => $endedAt->utc(),
            ])->save();

            return $shift->fresh();
        });
    }
}
