<?php

namespace App\Domain\Shift\Exceptions;

use DomainException;

class ShiftOwnershipDenied extends DomainException
{
    public static function forUserAndShift(int $userId, int $shiftId): self
    {
        return new self("User [{$userId}] is not allowed to manage shift [{$shiftId}].");
    }
}
