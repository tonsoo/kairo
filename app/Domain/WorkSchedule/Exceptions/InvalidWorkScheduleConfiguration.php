<?php

namespace App\Domain\WorkSchedule\Exceptions;

use DomainException;

class InvalidWorkScheduleConfiguration extends DomainException
{
    public static function invalidWeekday(int $weekday): self
    {
        return new self("Weekday [{$weekday}] must be between 1 and 7.");
    }

    public static function invalidType(string $type): self
    {
        return new self("Work schedule type [{$type}] is not supported.");
    }

    public static function expectedMinutesRequired(): self
    {
        return new self('Total time schedules require a positive expected minute value.');
    }

    public static function timeRangeRequiresBounds(): self
    {
        return new self('Time range schedules require both start and end times.');
    }

    public static function invalidTimeRange(): self
    {
        return new self('The work schedule time range must end after it starts.');
    }
}
