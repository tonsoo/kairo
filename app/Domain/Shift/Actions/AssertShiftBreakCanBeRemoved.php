<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\Exceptions\InvalidShiftBreakRemoval;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class AssertShiftBreakCanBeRemoved
{
    public function __construct(
        private AssertShiftBelongsToUser $assertShiftBelongsToUser,
    ) {}

    public function __invoke(User $user, Shift $previousShift, Shift $nextShift): void
    {
        ($this->assertShiftBelongsToUser)($user, $previousShift);
        ($this->assertShiftBelongsToUser)($user, $nextShift);

        if ($previousShift->id === $nextShift->id) {
            throw InvalidShiftBreakRemoval::identicalShifts();
        }

        if ($previousShift->ended_at === null) {
            throw InvalidShiftBreakRemoval::previousShiftMustBeCompleted();
        }

        $previousStartedAt = CarbonImmutable::instance($previousShift->started_at);
        $previousEndedAt = CarbonImmutable::instance($previousShift->ended_at);
        $nextStartedAt = CarbonImmutable::instance($nextShift->started_at);

        if ($previousStartedAt->greaterThanOrEqualTo($nextStartedAt)) {
            throw InvalidShiftBreakRemoval::invalidOrder();
        }

        if ($previousEndedAt->greaterThan($nextStartedAt)) {
            throw InvalidShiftBreakRemoval::invalidOrder();
        }
    }
}
