<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class ContinueShift
{
    public function __construct(
        private StartShift $startShift
    ) {}

    public function __invoke(User $user, CarbonImmutable $startedAt): Shift
    {
        return ($this->startShift)($user, $startedAt);
    }
}
