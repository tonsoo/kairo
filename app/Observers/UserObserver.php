<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\WorkSchedule\Actions\CreateDefaultWorkSchedulesForUser;
use App\Models\User;

final readonly class UserObserver
{
    public function __construct(
        private CreateDefaultWorkSchedulesForUser $createDefaultWorkSchedulesForUser,
    ) {}

    public function created(User $user): void
    {
        ($this->createDefaultWorkSchedulesForUser)($user);
    }
}
