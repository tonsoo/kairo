<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\Exceptions\ShiftOwnershipDenied;
use App\Models\Shift;
use App\Models\User;

final readonly class AssertShiftBelongsToUser
{
    public function __invoke(User $user, Shift $shift): void
    {
        if ($shift->user_id !== $user->id) {
            throw ShiftOwnershipDenied::forUserAndShift($user->id, $shift->id);
        }
    }
}
