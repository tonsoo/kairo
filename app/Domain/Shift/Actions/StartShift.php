<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Domain\WorkSchedule\Actions\BuildDailyWorkScheduleSnapshot;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class StartShift
{
    public function __construct(
        private AssertNoOpenShift $assertNoOpenShift,
        private AssertShiftDoesNotOverlap $assertShiftDoesNotOverlap,
        private BuildDailyWorkScheduleSnapshot $buildDailyWorkScheduleSnapshot,
    ) {}

    public function __invoke(User $user, CarbonImmutable $startedAt): Shift
    {
        $period = new ShiftPeriodData($startedAt, null);

        ($this->assertNoOpenShift)($user);
        ($this->assertShiftDoesNotOverlap)($user, $period);

        $shift = DB::transaction(function () use ($period, $startedAt, $user): Shift {
            ($this->buildDailyWorkScheduleSnapshot)($user, $startedAt);

            return Shift::query()->create([
                'user_id' => $user->id,
                'started_at' => $period->startedAt->utc(),
            ]);
        });

        return $shift->fresh();
    }
}
