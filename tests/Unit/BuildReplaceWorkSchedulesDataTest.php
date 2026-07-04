<?php

declare(strict_types=1);

use App\Domain\WorkSchedule\Actions\BuildReplaceWorkSchedulesData;
use App\Domain\WorkSchedule\DTOs\ReplaceWorkScheduleEntryData;
use App\Domain\WorkSchedule\DTOs\ReplaceWorkSchedulesData;
use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Models\User;

test('it builds typed replace work schedules data from validated input', function () {
    $user = new User([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $data = (new BuildReplaceWorkSchedulesData)($user, [
        'effective_from' => '2026-06-29',
        'schedules' => [
            [
                'weekday' => 1,
                'type' => 'total_time',
                'expected_minutes' => 480,
            ],
            [
                'weekday' => 2,
                'type' => 'time_range',
                'starts_at' => '09:00',
                'ends_at' => '18:00',
            ],
            [
                'weekday' => 6,
                'type' => 'day_off',
            ],
        ],
    ]);

    expect($data)
        ->toBeInstanceOf(ReplaceWorkSchedulesData::class)
        ->and($data->effectiveFrom->format('Y-m-d H:i:s'))->toBe('2026-06-29 00:00:00')
        ->and($data->effectiveFrom->timezoneName)->toBe('America/Sao_Paulo')
        ->and($data->schedules)->toHaveCount(3)
        ->and($data->schedules->first())->toBeInstanceOf(ReplaceWorkScheduleEntryData::class)
        ->and($data->schedules->first()->type)->toBe(WorkScheduleType::totalTime)
        ->and($data->schedules->get(1)?->type)->toBe(WorkScheduleType::timeRange)
        ->and($data->schedules->last()?->type)->toBe(WorkScheduleType::dayOff);
});

test('it converts typed replacement data into domain work schedule data', function () {
    $user = new User([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $replaceWorkSchedulesData = (new BuildReplaceWorkSchedulesData)($user, [
        'effective_from' => '2026-06-29',
        'schedules' => [
            [
                'weekday' => 2,
                'type' => 'time_range',
                'starts_at' => '09:00',
                'ends_at' => '18:00',
            ],
        ],
    ]);

    $workScheduleData = $replaceWorkSchedulesData->toWorkScheduleData($user)->sole();

    expect($workScheduleData->type)
        ->toBe(WorkScheduleType::timeRange)
        ->and($workScheduleData->weekday)->toBe(2)
        ->and($workScheduleData->expectedMinutes)->toBe(540)
        ->and($workScheduleData->startsAt?->format('Y-m-d H:i:s'))->toBe('2026-06-29 09:00:00')
        ->and($workScheduleData->endsAt?->format('Y-m-d H:i:s'))->toBe('2026-06-29 18:00:00')
        ->and($workScheduleData->effectiveFrom->format('Y-m-d H:i:s'))->toBe('2026-06-29 00:00:00');
});
