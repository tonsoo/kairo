<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\DTOs;

use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Domain\WorkSchedule\Exceptions\InvalidWorkScheduleConfiguration;
use Carbon\CarbonImmutable;

final readonly class WorkScheduleData
{
    public function __construct(
        public int $weekday,
        public WorkScheduleType $type,
        public int $expectedMinutes,
        public ?CarbonImmutable $startsAt,
        public ?CarbonImmutable $endsAt,
        public CarbonImmutable $effectiveFrom,
    ) {}

    public static function totalTime(int $weekday, int $expectedMinutes, CarbonImmutable $effectiveFrom): self
    {
        self::assertWeekday($weekday);

        if ($expectedMinutes <= 0) {
            throw InvalidWorkScheduleConfiguration::expectedMinutesRequired();
        }

        return new self(
            weekday: $weekday,
            type: WorkScheduleType::totalTime,
            expectedMinutes: $expectedMinutes,
            startsAt: null,
            endsAt: null,
            effectiveFrom: $effectiveFrom,
        );
    }

    public static function timeRange(
        int $weekday,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        CarbonImmutable $effectiveFrom,
    ): self {
        self::assertWeekday($weekday);

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            throw InvalidWorkScheduleConfiguration::invalidTimeRange();
        }

        return new self(
            weekday: $weekday,
            type: WorkScheduleType::timeRange,
            expectedMinutes: (int) $startsAt->diffInMinutes($endsAt),
            startsAt: $startsAt,
            endsAt: $endsAt,
            effectiveFrom: $effectiveFrom,
        );
    }

    public static function dayOff(int $weekday, CarbonImmutable $effectiveFrom): self
    {
        self::assertWeekday($weekday);

        return new self(
            weekday: $weekday,
            type: WorkScheduleType::dayOff,
            expectedMinutes: 0,
            startsAt: null,
            endsAt: null,
            effectiveFrom: $effectiveFrom,
        );
    }

    private static function assertWeekday(int $weekday): void
    {
        if ($weekday < 1 || $weekday > 7) {
            throw InvalidWorkScheduleConfiguration::invalidWeekday($weekday);
        }
    }
}
