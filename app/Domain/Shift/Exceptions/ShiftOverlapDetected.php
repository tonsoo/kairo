<?php

namespace App\Domain\Shift\Exceptions;

use DomainException;

class ShiftOverlapDetected extends DomainException
{
    public static function forUser(int $userId): self
    {
        return new self("User [{$userId}] already has another shift in the requested period.");
    }
}
