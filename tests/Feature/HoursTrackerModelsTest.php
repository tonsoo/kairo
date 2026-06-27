<?php

use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Models\DailyWorkSchedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('user exposes hours tracker relationships', function () {
    CarbonImmutable::setTestNow('2026-06-01 00:00:00 UTC');

    $user = User::factory()->create();

    CarbonImmutable::setTestNow();

    $workSchedule = WorkSchedule::factory()->timeRange()->forWeekday(7)->for($user)->create([
        'effective_from' => '2026-06-27',
    ]);
    $dailyWorkSchedule = DailyWorkSchedule::factory()->fromWorkSchedule($workSchedule, '2026-06-23')->create();
    $shift = Shift::factory()->for($user)->create();

    $user->load('workSchedules', 'dailyWorkSchedules', 'shifts');

    expect($user->workSchedules->contains(fn (WorkSchedule $candidate): bool => $candidate->is($workSchedule)))->toBeTrue()
        ->and($user->dailyWorkSchedules)->toHaveCount(1)
        ->and($user->dailyWorkSchedules->first()?->is($dailyWorkSchedule))->toBeTrue()
        ->and($user->shifts)->toHaveCount(1)
        ->and($user->shifts->first()?->is($shift))->toBeTrue()
        ->and($workSchedule->user->is($user))->toBeTrue()
        ->and($dailyWorkSchedule->user->is($user))->toBeTrue()
        ->and($dailyWorkSchedule->workSchedule?->is($workSchedule))->toBeTrue()
        ->and($shift->user->is($user))->toBeTrue();
});

test('new users receive default weekly work schedules', function () {
    CarbonImmutable::setTestNow('2026-06-01 00:00:00 UTC');

    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    CarbonImmutable::setTestNow();

    $schedules = $user->workSchedules()
        ->whereDate('effective_from', '2026-06-01')
        ->orderBy('weekday')
        ->get()
        ->keyBy('weekday');

    $mondaySchedule = $schedules->get(1);
    $saturdaySchedule = $schedules->get(6);

    expect($schedules)->toHaveCount(7)
        ->and($mondaySchedule?->type)->toBe(WorkScheduleType::timeRange)
        ->and($mondaySchedule?->expected_minutes)->toBe(480)
        ->and($mondaySchedule?->starts_at)->toBe('09:00:00')
        ->and($mondaySchedule?->ends_at)->toBe('17:00:00')
        ->and($saturdaySchedule?->type)->toBe(WorkScheduleType::dayOff)
        ->and($saturdaySchedule?->expected_minutes)->toBe(0)
        ->and($saturdaySchedule?->starts_at)->toBeNull()
        ->and($saturdaySchedule?->ends_at)->toBeNull();
});

test('hours tracker models cast temporal fields and expose shift scopes', function () {
    $workSchedule = WorkSchedule::factory()->create([
        'effective_from' => '2026-06-01',
    ]);
    $dailyWorkSchedule = DailyWorkSchedule::factory()->create([
        'date' => '2026-06-24',
    ]);
    $ongoingShift = Shift::factory()->ongoing()->create();
    $completedShift = Shift::factory()->create();

    expect($workSchedule->effective_from)->toBeInstanceOf(\DateTimeInterface::class)
        ->and($dailyWorkSchedule->date)->toBeInstanceOf(\DateTimeInterface::class)
        ->and($ongoingShift->started_at)->toBeInstanceOf(\DateTimeInterface::class)
        ->and($completedShift->ended_at)->toBeInstanceOf(\DateTimeInterface::class)
        ->and(Shift::query()->ongoing()->pluck('id')->all())->toContain($ongoingShift->id)
        ->and(Shift::query()->completed()->pluck('id')->all())->toContain($completedShift->id)
        ->and(Shift::query()->completed()->pluck('id')->all())->not->toContain($ongoingShift->id);
});
