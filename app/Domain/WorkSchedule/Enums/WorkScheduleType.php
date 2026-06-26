<?php

namespace App\Domain\WorkSchedule\Enums;

enum WorkScheduleType: string
{
    case totalTime = 'total_time';

    case timeRange = 'time_range';

    case dayOff = 'day_off';
}
