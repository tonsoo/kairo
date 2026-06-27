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

        if ($previousShift->is($nextShift)) {
            throw InvalidShiftBreakRemoval::identicalShifts();
        }

        if ($previousShift->ended_at === null) {
            throw InvalidShiftBreakRemoval::previousShiftMustBeCompleted();
        }

        if ($previousShift->started_at->greaterThanOrEqualTo($nextShift->started_at)) {
            throw InvalidShiftBreakRemoval::invalidOrder();
        }

        if ($previousShift->ended_at->greaterThan($nextShift->started_at)) {
            throw InvalidShiftBreakRemoval::invalidOrder();
        }
    }
}
