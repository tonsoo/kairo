<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class StartShift
{
    public function __construct(
        private AssertNoOpenShift $assertNoOpenShift,
        private AssertShiftDoesNotOverlap $assertShiftDoesNotOverlap,
    ) {}

    public function __invoke(User $user, CarbonImmutable $startedAt): Shift
    {
        $period = new ShiftPeriodData($startedAt, null);

        ($this->assertNoOpenShift)($user);
        ($this->assertShiftDoesNotOverlap)($user, $period);

        $shift = Shift::query()->create([
            'user_id' => $user->id,
            'started_at' => $period->startedAt->utc(),
        ]);

        return $shift->fresh();
    }
}
