<?php

namespace App\Domain\Shift\Exceptions;

use DomainException;

class OngoingShiftAlreadyExists extends DomainException
{
    public static function forUser(int $userId): self
    {
        return new self("User [{$userId}] already has an ongoing shift.");
    }
}
