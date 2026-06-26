<?php

namespace App\Domain\Shift\Exceptions;

use DomainException;

class NoOngoingShiftFound extends DomainException
{
    public static function forUser(int $userId): self
    {
        return new self("User [{$userId}] does not have an ongoing shift.");
    }
}
