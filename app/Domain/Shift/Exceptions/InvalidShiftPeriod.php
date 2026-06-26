<?php

namespace App\Domain\Shift\Exceptions;

use DomainException;

class InvalidShiftPeriod extends DomainException
{
    public static function chronology(): self
    {
        return new self('The shift end time must be after the shift start time.');
    }
}
