<?php

use App\Domain\WorkSchedule\Actions\BuildDailyWorkScheduleSnapshot;
use App\Domain\WorkSchedule\Actions\GetEffectiveWorkScheduleForDate;
use App\Domain\WorkSchedule\Actions\UpsertWorkSchedule;
use App\Domain\WorkSchedule\DTOs\WorkScheduleData;
use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Domain\WorkSchedule\Exceptions\InvalidWorkScheduleConfiguration;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('upsert work schedule stores total time schedules', function () {
    $user = User::factory()->create();
    $data = WorkScheduleData::totalTime(1, 480, CarbonImmutable::parse('2026-06-01', 'UTC'));

    $workSchedule = app(UpsertWorkSchedule::class)($user, $data);

    expect($workSchedule->user_id)->toBe($user->id)
        ->and($workSchedule->weekday)->toBe(1)
        ->and($workSchedule->type)->toBe(WorkScheduleType::totalTime)
        ->and($workSchedule->expected_minutes)->toBe(480)
        ->and($workSchedule->starts_at)->toBeNull()
        ->and($workSchedule->ends_at)->toBeNull();
});

test('upsert work schedule updates an existing schedule for the same effective date', function () {
    $user = User::factory()->create();
    $existingWorkSchedule = WorkSchedule::factory()->for($user)->create([
        'weekday' => 1,
        'effective_from' => '2026-06-29',
        'type' => 'total_time',
        'expected_minutes' => 480,
    ]);

    $updatedWorkSchedule = app(UpsertWorkSchedule::class)($user, WorkScheduleData::timeRange(
        1,
        CarbonImmutable::parse('2026-06-29 09:00:00', 'UTC'),
        CarbonImmutable::parse('2026-06-29 18:00:00', 'UTC'),
        CarbonImmutable::parse('2026-06-29', 'UTC'),
    ));

    expect($updatedWorkSchedule->id)->toBe($existingWorkSchedule->id)
        ->and($updatedWorkSchedule->type)->toBe(WorkScheduleType::timeRange)
        ->and($updatedWorkSchedule->expected_minutes)->toBe(540)
        ->and($updatedWorkSchedule->starts_at)->toBe('09:00:00')
        ->and($updatedWorkSchedule->ends_at)->toBe('18:00:00')
        ->and(WorkSchedule::query()
            ->where('user_id', $user->id)
            ->where('weekday', 1)
            ->whereDate('effective_from', '2026-06-29')
            ->count())->toBe(1);
});

test('upsert work schedule computes expected minutes for time ranges', function () {
    $user = User::factory()->create();
    $data = WorkScheduleData::timeRange(
        2,
        CarbonImmutable::parse('2026-06-01 09:00:00', 'UTC'),
        CarbonImmutable::parse('2026-06-01 18:00:00', 'UTC'),
        CarbonImmutable::parse('2026-06-01', 'UTC'),
    );

    $workSchedule = app(UpsertWorkSchedule::class)($user, $data);

    expect($workSchedule->type)->toBe(WorkScheduleType::timeRange)
        ->and($workSchedule->expected_minutes)->toBe(540)
        ->and($workSchedule->starts_at)->toBe('09:00:00')
        ->and($workSchedule->ends_at)->toBe('18:00:00');
});

test('effective work schedule picks the latest schedule version for the date', function () {
    $user = User::factory()->create();

    app(UpsertWorkSchedule::class)($user, WorkScheduleData::totalTime(1, 420, CarbonImmutable::parse('2026-06-01', 'UTC')));
    $latestSchedule = app(UpsertWorkSchedule::class)($user, WorkScheduleData::totalTime(1, 480, CarbonImmutable::parse('2026-06-15', 'UTC')));

    $resolvedSchedule = app(GetEffectiveWorkScheduleForDate::class)($user, CarbonImmutable::parse('2026-06-22', 'UTC'));

    expect($resolvedSchedule?->is($latestSchedule))->toBeTrue();
});

test('build daily work schedule snapshot mirrors the effective schedule', function () {
    $user = User::factory()->create();

    $workSchedule = app(UpsertWorkSchedule::class)($user, WorkScheduleData::timeRange(
        4,
        CarbonImmutable::parse('2026-06-01 08:30:00', 'America/Sao_Paulo'),
        CarbonImmutable::parse('2026-06-01 17:30:00', 'America/Sao_Paulo'),
        CarbonImmutable::parse('2026-06-01', 'America/Sao_Paulo'),
    ));

    $snapshot = app(BuildDailyWorkScheduleSnapshot::class)($user, CarbonImmutable::parse('2026-06-25', 'America/Sao_Paulo'));

    expect($snapshot)->not->toBeNull()
        ->and($snapshot?->work_schedule_id)->toBe($workSchedule->id)
        ->and($snapshot?->expected_minutes)->toBe(540)
        ->and($snapshot?->starts_at)->toBe('08:30:00')
        ->and($snapshot?->ends_at)->toBe('17:30:00');
});

test('invalid work schedule data is rejected', function () {
    expect(fn () => WorkScheduleData::totalTime(1, 0, CarbonImmutable::parse('2026-06-01', 'UTC')))
        ->toThrow(InvalidWorkScheduleConfiguration::class)
        ->and(fn () => WorkScheduleData::timeRange(
            1,
            CarbonImmutable::parse('2026-06-01 18:00:00', 'UTC'),
            CarbonImmutable::parse('2026-06-01 09:00:00', 'UTC'),
            CarbonImmutable::parse('2026-06-01', 'UTC'),
        ))->toThrow(InvalidWorkScheduleConfiguration::class);

});
