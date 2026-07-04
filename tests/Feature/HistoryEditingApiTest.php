<?php

declare(strict_types=1);

use App\Models\DailyWorkSchedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('authenticated users can fetch a missing daily work schedule for a date', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.daily-work-schedules.show', ['date' => '2026-06-25']))
        ->assertOk()
        ->assertJsonPath('data', null);
});

test('authenticated users can upsert a daily work schedule override', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $this->actingAs($user)
        ->putJson(route('api.me.daily-work-schedules.upsert', ['date' => '2026-06-25']), [
            'type' => 'time_range',
            'starts_at' => '08:30',
            'ends_at' => '17:30',
        ])
        ->assertOk()
        ->assertJsonPath('data.date', '2026-06-25')
        ->assertJsonPath('data.weekday', 4)
        ->assertJsonPath('data.type', 'time_range')
        ->assertJsonPath('data.expected_minutes', 540)
        ->assertJsonPath('data.starts_at', '08:30')
        ->assertJsonPath('data.ends_at', '17:30');

    expect(DailyWorkSchedule::query()
        ->where('user_id', $user->id)
        ->whereDate('date', '2026-06-25')
        ->first())
        ->not->toBeNull();
});

test('authenticated users can create manual shifts from history editing', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    $this->actingAs($user)
        ->postJson(route('api.me.shifts.store'), [
            'started_at' => '2026-06-25T09:00:00+00:00',
            'ended_at' => '2026-06-25T17:00:00+00:00',
            'timezone' => 'UTC',
        ])
        ->assertOk()
        ->assertJsonPath('data.started_at', '2026-06-25T09:00:00+00:00')
        ->assertJsonPath('data.ended_at', '2026-06-25T17:00:00+00:00');

    expect(Shift::query()->whereBelongsTo($user)->count())->toBe(1);
});

test('creating a manual shift snapshots the effective daily work schedule when missing', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    WorkSchedule::factory()->for($user)->create([
        'weekday' => 4,
        'effective_from' => '2026-06-01',
        'type' => 'time_range',
        'expected_minutes' => 480,
        'starts_at' => '09:00:00',
        'ends_at' => '18:00:00',
    ]);

    $this->actingAs($user)
        ->postJson(route('api.me.shifts.store'), [
            'started_at' => '2026-06-25T09:00:00-03:00',
            'ended_at' => '2026-06-25T17:00:00-03:00',
            'timezone' => 'America/Sao_Paulo',
        ])
        ->assertOk();

    expect(DailyWorkSchedule::query()
        ->where('user_id', $user->id)
        ->whereDate('date', '2026-06-25')
        ->first())
        ->not->toBeNull()
        ->type->value->toBe('time_range')
        ->expected_minutes->toBe(480)
        ->starts_at->toBe('09:00:00')
        ->ends_at->toBe('18:00:00');
});

test('creating a manual shift snapshots the earliest weekday schedule for dates before the first schedule version', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    WorkSchedule::factory()->for($user)->create([
        'weekday' => 4,
        'effective_from' => '2026-06-01',
        'type' => 'total_time',
        'expected_minutes' => 480,
        'starts_at' => null,
        'ends_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson(route('api.me.shifts.store'), [
            'started_at' => '2026-04-02T09:00:00-03:00',
            'ended_at' => '2026-04-02T17:00:00-03:00',
            'timezone' => 'America/Sao_Paulo',
        ])
        ->assertOk();

    expect(DailyWorkSchedule::query()
        ->where('user_id', $user->id)
        ->whereDate('date', '2026-04-02')
        ->first())
        ->not->toBeNull()
        ->type->value->toBe('total_time')
        ->expected_minutes->toBe(480)
        ->starts_at->toBeNull()
        ->ends_at->toBeNull();
});

test('hours summary exposes scheduled history days even without shifts', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    WorkSchedule::factory()->for($user)->create([
        'weekday' => 4,
        'effective_from' => '2026-06-01',
        'type' => 'total_time',
        'expected_minutes' => 480,
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.hours-summary', [
            'at' => '2026-06-25T12:00:00+00:00',
            'month' => '2026-06-01',
            'timezone' => 'UTC',
        ]))
        ->assertOk()
        ->assertJsonFragment([
            'date' => '2026-06-25',
            'worked_minutes' => 0,
            'regular_minutes' => 0,
            'extra_minutes' => 0,
            'missing_minutes' => 480,
        ]);
});
