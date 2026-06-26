<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\Exceptions\OngoingShiftAlreadyExists;
use App\Models\Shift;
use App\Models\User;

final readonly class AssertNoOpenShift
{
    public function __invoke(User $user): void
    {
        if (Shift::query()->where('user_id', $user->id)->ongoing()->exists()) {
            throw OngoingShiftAlreadyExists::forUser($user->id);
        }
    }
}
