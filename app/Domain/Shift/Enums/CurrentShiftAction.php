<?php

namespace App\Domain\Shift\Enums;

enum CurrentShiftAction: string
{
    case start = 'start';

    case end = 'end';

    case continue = 'continue';
}
