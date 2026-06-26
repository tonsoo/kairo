<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class DeleteShift
{
    public function __construct(
        private AssertShiftBelongsToUser $assertShiftBelongsToUser
    ) {}

    public function __invoke(User $user, Shift $shift): void
    {
        ($this->assertShiftBelongsToUser)($user, $shift);

        DB::transaction(function () use ($shift) {
            $shift->delete();
        });
    }
}
