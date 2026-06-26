<?php

use App\Models\DailyWorkSchedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('user exposes hours tracker relationships', function () {
    $user = User::factory()->create();
    $workSchedule = WorkSchedule::factory()->timeRange()->for($user)->create();
    $dailyWorkSchedule = DailyWorkSchedule::factory()->fromWorkSchedule($workSchedule, '2026-06-23')->create();
    $shift = Shift::factory()->for($user)->create();

    $user->load('workSchedules', 'dailyWorkSchedules', 'shifts');

    expect($user->workSchedules)->toHaveCount(1)
        ->and($user->workSchedules->first()?->is($workSchedule))->toBeTrue()
        ->and($user->dailyWorkSchedules)->toHaveCount(1)
        ->and($user->dailyWorkSchedules->first()?->is($dailyWorkSchedule))->toBeTrue()
        ->and($user->shifts)->toHaveCount(1)
        ->and($user->shifts->first()?->is($shift))->toBeTrue()
        ->and($workSchedule->user->is($user))->toBeTrue()
        ->and($dailyWorkSchedule->user->is($user))->toBeTrue()
        ->and($dailyWorkSchedule->workSchedule?->is($workSchedule))->toBeTrue()
        ->and($shift->user->is($user))->toBeTrue();
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
