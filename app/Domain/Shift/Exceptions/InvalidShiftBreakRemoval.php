<?php

declare(strict_types=1);

namespace App\Domain\Shift\Exceptions;

use DomainException;

final class InvalidShiftBreakRemoval extends DomainException
{
    public static function identicalShifts(): self
    {
        return new self('The selected shifts must be different.');
    }

    public static function previousShiftMustBeCompleted(): self
    {
        return new self('The previous shift must be completed before removing the break.');
    }

    public static function invalidOrder(): self
    {
        return new self('The selected shifts cannot be merged in the provided order.');
    }
}
