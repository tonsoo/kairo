<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\DTOs;

use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Domain\WorkSchedule\Exceptions\InvalidWorkScheduleConfiguration;
use App\Models\User;
use App\Support\Parsing\DateParser;
use Carbon\CarbonImmutable;

final readonly class ReplaceWorkScheduleEntryData
{
    public function __construct(
        public int $weekday,
        public WorkScheduleType $type,
        public ?int $expectedMinutes,
        public ?string $startsAt,
        public ?string $endsAt,
    ) {}

    public function toWorkScheduleData(User $user, CarbonImmutable $effectiveFrom): WorkScheduleData
    {
        return match ($this->type) {
            WorkScheduleType::totalTime => WorkScheduleData::totalTime(
                weekday: $this->weekday,
                expectedMinutes: $this->expectedMinutes ?? throw InvalidWorkScheduleConfiguration::expectedMinutesRequired(),
                effectiveFrom: $effectiveFrom,
            ),
            WorkScheduleType::timeRange => WorkScheduleData::timeRange(
                weekday: $this->weekday,
                startsAt: DateParser::parseLocalTimeOnDate(
                    $this->startsAt ?? throw InvalidWorkScheduleConfiguration::timeRangeRequiresBounds(),
                    $effectiveFrom,
                    $user->timezone,
                    'starts_at',
                ),
                endsAt: DateParser::parseLocalTimeOnDate(
                    $this->endsAt ?? throw InvalidWorkScheduleConfiguration::timeRangeRequiresBounds(),
                    $effectiveFrom,
                    $user->timezone,
                    'ends_at',
                ),
                effectiveFrom: $effectiveFrom,
            ),
        };
    }
}
