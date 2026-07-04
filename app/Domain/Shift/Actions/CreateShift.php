<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Domain\WorkSchedule\Actions\CreateDailyWorkScheduleSnapshot;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateShift
{
    public function __construct(
        private AssertShiftDoesNotOverlap $assertShiftDoesNotOverlap,
        private CreateDailyWorkScheduleSnapshot $createDailyWorkScheduleSnapshot,
    ) {}

    public function __invoke(User $user, ShiftPeriodData $period): Shift
    {
        ($this->assertShiftDoesNotOverlap)($user, $period);

        /** @var Shift $shift */
        $shift = DB::transaction(function () use ($period, $user): Shift {
            ($this->createDailyWorkScheduleSnapshot)(
                $user,
                $period->startedAt->setTimezone($user->timezone),
            );

            return Shift::query()->create([
                'user_id' => $user->id,
                'started_at' => $period->startedAt->utc(),
                'ended_at' => $period->endedAt?->utc(),
            ]);
        });

        return $shift->fresh();
    }
}
