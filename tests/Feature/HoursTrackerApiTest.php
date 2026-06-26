<?php

declare(strict_types=1);

use App\Domain\WorkSchedule\Actions\UpsertWorkSchedule;
use App\Domain\WorkSchedule\DTOs\WorkScheduleData;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests cannot access the hours tracker api', function () {
    $this->getJson(route('api.me.current-shift-state'))
        ->assertUnauthorized();

    $this->getJson(route('api.me.hours-summary'))
        ->assertUnauthorized();
});

test('authenticated users can fetch the dashboard overview', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    foreach (range(1, 5) as $weekday) {
        WorkSchedule::factory()->for($user)->create([
            'weekday' => $weekday,
            'effective_from' => '2026-06-25',
            'type' => 'total_time',
            'expected_minutes' => 480,
        ]);
    }

    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
        'ended_at' => '2026-06-25 15:00:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 16:00:00',
        'ended_at' => '2026-06-25 21:00:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-26 12:00:00',
        'ended_at' => '2026-06-26 15:00:00',
    ]);
    Shift::factory()->ongoing()->for($user)->create([
        'started_at' => '2026-06-26 16:00:00',
        'ended_at' => null,
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.hours-summary', [
            'at' => '2026-06-26T15:00:00-03:00',
        ]))
        ->assertOk()
        ->assertJsonPath('data.balance.balance_minutes', 0)
        ->assertJsonPath('data.balance.positive_minutes', 0)
        ->assertJsonPath('data.balance.negative_minutes', 0)
        ->assertJsonPath('data.today.date', '2026-06-26')
        ->assertJsonPath('data.today.worked_minutes', 300)
        ->assertJsonPath('data.today.paused_minutes', 60)
        ->assertJsonPath('data.today.expected_minutes', 480)
        ->assertJsonPath('data.today.missing_minutes', 180)
        ->assertJsonCount(6, 'data.semester.items')
        ->assertJsonPath('data.month.balance_minutes', -180)
        ->assertJsonCount(26, 'data.month.items')
        ->assertJsonFragment([
            'date' => '2026-06-01',
            'worked_minutes' => 780,
            'regular_minutes' => 780,
            'extra_minutes' => 0,
            'missing_minutes' => 180,
        ])
        ->assertJsonFragment([
            'date' => '2026-06-25',
            'worked_minutes' => 480,
            'regular_minutes' => 480,
            'extra_minutes' => 0,
            'missing_minutes' => 0,
        ]);
});

test('dashboard hours summary prefers daily work schedule snapshots over updated weekly schedules', function () {
    CarbonImmutable::setTestNow('2026-06-26 00:00:00 UTC');

    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    CarbonImmutable::setTestNow();

    $this->artisan('hours-tracker:snapshot-daily-work-schedules', [
        '--date' => '2026-06-26',
    ])->assertSuccessful();

    app(UpsertWorkSchedule::class)($user, WorkScheduleData::totalTime(
        5,
        300,
        CarbonImmutable::parse('2026-06-26', 'UTC'),
    ));

    $this->actingAs($user)
        ->getJson(route('api.me.hours-summary', [
            'at' => '2026-06-26T12:00:00+00:00',
        ]))
        ->assertOk()
        ->assertJsonPath('data.today.expected_minutes', 480)
        ->assertJsonPath('data.today.missing_minutes', 480);
});


test('dashboard hours summary isolates overnight shift minutes using the requested timezone', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    WorkSchedule::factory()->for($user)->create([
        'weekday' => 4,
        'effective_from' => '2026-06-25',
        'type' => 'total_time',
        'expected_minutes' => 480,
    ]);
    WorkSchedule::factory()->for($user)->create([
        'weekday' => 5,
        'effective_from' => '2026-06-25',
        'type' => 'total_time',
        'expected_minutes' => 480,
    ]);

    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 13:00:00',
        'ended_at' => '2026-06-26 13:00:00',
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.hours-summary', [
            'at' => '2026-06-26T10:43:00-03:00',
            'timezone' => 'America/Sao_Paulo',
        ]))
        ->assertOk()
        ->assertJsonPath('data.timezone', 'America/Sao_Paulo')
        ->assertJsonPath('data.balance.balance_minutes', 360)
        ->assertJsonPath('data.today.date', '2026-06-26')
        ->assertJsonPath('data.today.worked_minutes', 600)
        ->assertJsonPath('data.today.paused_minutes', 43)
        ->assertJsonPath('data.today.extra_minutes', 120)
        ->assertJsonPath('data.today.missing_minutes', 0);
});

test('dashboard overview rejects invalid datetime formats', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('api.me.hours-summary', [
            'at' => '2026-06-26 15:00:00',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('at');
});

test('authenticated users can fetch traversed dashboard periods', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    foreach (range(1, 5) as $weekday) {
        WorkSchedule::factory()->for($user)->create([
            'weekday' => $weekday,
            'effective_from' => '2025-12-01',
            'type' => 'total_time',
            'expected_minutes' => 480,
        ]);
    }

    Shift::factory()->for($user)->create([
        'started_at' => '2025-12-15 12:00:00',
        'ended_at' => '2025-12-15 20:00:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-05-12 12:00:00',
        'ended_at' => '2026-05-12 20:00:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-26 12:00:00',
        'ended_at' => '2026-06-26 15:00:00',
    ]);

    $defaultResponse = $this->actingAs($user)
        ->getJson(route('api.me.hours-summary', [
            'at' => '2026-06-26T15:00:00-03:00',
        ]))
        ->assertOk();

    $traversedResponse = $this->actingAs($user)
        ->getJson(route('api.me.hours-summary', [
            'at' => '2026-06-26T15:00:00-03:00',
            'month' => '2026-05-01',
            'semester_start' => '2025-12-01',
        ]))
        ->assertOk()
        ->assertJsonPath('data.today.date', '2026-06-26')
        ->assertJsonPath('data.month.starts_at', '2026-05-01')
        ->assertJsonPath('data.month.ends_at', '2026-05-31')
        ->assertJsonPath('data.semester.starts_at', '2025-12-01')
        ->assertJsonPath('data.semester.ends_at', '2026-05-31')
        ->assertJsonCount(31, 'data.month.items')
        ->assertJsonCount(6, 'data.semester.items')
        ->assertJsonFragment([
            'date' => '2026-05-01',
            'worked_minutes' => 480,
            'regular_minutes' => 480,
            'extra_minutes' => 0,
        ])
        ->assertJsonFragment([
            'date' => '2025-12-01',
            'worked_minutes' => 480,
            'regular_minutes' => 480,
            'extra_minutes' => 0,
        ]);

    expect($traversedResponse->json('data.today'))->toBe($defaultResponse->json('data.today'))
        ->and($traversedResponse->json('data.balance'))->toBe($defaultResponse->json('data.balance'));
});

test('dashboard overview rejects future month and semester periods', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.hours-summary', [
            'at' => '2026-06-26T15:00:00-03:00',
            'month' => '2026-07-01',
            'semester_start' => '2026-02-01',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'month',
            'semester_start',
        ]);
});

test('authenticated users can fetch the current shift state', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    Shift::factory()->ongoing()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.current-shift-state', [
            'at' => '2026-06-25T13:00:00-03:00',
        ]))
        ->assertOk()
        ->assertJsonPath('data.next_action', 'end')
        ->assertJsonPath('data.has_ongoing_shift', true)
        ->assertJsonPath('data.active_shift.started_at', '2026-06-25T09:00:00-03:00');
});

test('current shift state rejects invalid datetime formats', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('api.me.current-shift-state', [
            'at' => '2026-06-25 13:00:00',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('at');
});

test('authenticated users can list shifts within a date range including ongoing shifts', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-24 12:00:00',
        'ended_at' => '2026-06-24 21:00:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
        'ended_at' => '2026-06-25 21:00:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-26 13:00:00',
        'ended_at' => null,
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-27 01:00:00',
        'ended_at' => '2026-06-27 05:00:00',
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.shifts.index', [
            'from' => '2026-06-25',
            'to' => '2026-06-26',
        ]))
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.started_at', '2026-06-26T22:00:00-03:00')
        ->assertJsonPath('data.0.ended_at', '2026-06-27T02:00:00-03:00')
        ->assertJsonPath('data.1.ended_at', null)
        ->assertJsonPath('data.1.started_at', '2026-06-26T10:00:00-03:00')
        ->assertJsonPath('data.2.started_at', '2026-06-25T09:00:00-03:00')
        ->assertJsonPath('data.2.ended_at', '2026-06-25T18:00:00-03:00');
});


test('authenticated users can list overnight shifts within a local date range', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-24 12:00:00',
        'ended_at' => '2026-06-24 21:00:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 13:00:00',
        'ended_at' => '2026-06-26 13:00:00',
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.shifts.index', [
            'from' => '2026-06-26',
            'to' => '2026-06-26',
            'timezone' => 'America/Sao_Paulo',
        ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.timezone', 'America/Sao_Paulo')
        ->assertJsonPath('data.0.started_at', '2026-06-25T10:00:00-03:00')
        ->assertJsonPath('data.0.ended_at', '2026-06-26T10:00:00-03:00');
});

test('authenticated users can start and end shifts through the api', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $this->actingAs($user)
        ->postJson(route('api.me.shifts.start'), [
            'at' => '2026-06-25T09:00:00-03:00',
        ])
        ->assertCreated()
        ->assertJsonPath('data.started_at', '2026-06-25T09:00:00-03:00')
        ->assertJsonPath('data.ended_at', null);

    $this->actingAs($user)
        ->postJson(route('api.me.shifts.end'), [
            'at' => '2026-06-25T18:00:00-03:00',
        ])
        ->assertOk()
        ->assertJsonPath('data.ended_at', '2026-06-25T18:00:00-03:00');

    expect($user->shifts()->count())->toBe(1);
});

test('users cannot manage another users shifts through the api', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $shift = Shift::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->patchJson(route('api.me.shifts.update', $shift), [
            'started_at' => '2026-06-25T09:00:00+00:00',
            'ended_at' => '2026-06-25T10:00:00+00:00',
        ])
        ->assertForbidden();
});

test('users can replace work schedules for an effective date', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    WorkSchedule::factory()->for($user)->create([
        'weekday' => 1,
        'effective_from' => '2026-06-29',
        'type' => 'total_time',
        'expected_minutes' => 480,
    ]);
    WorkSchedule::factory()->for($user)->create([
        'weekday' => 2,
        'effective_from' => '2026-06-29',
        'type' => 'total_time',
        'expected_minutes' => 480,
    ]);

    $this->actingAs($user)
        ->putJson(route('api.me.work-schedules.replace'), [
            'effective_from' => '2026-06-29',
            'schedules' => [
                [
                    'weekday' => 1,
                    'type' => 'time_range',
                    'starts_at' => '09:00',
                    'ends_at' => '18:00',
                ],
            ],
        ])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.weekday', 1)
        ->assertJsonPath('data.0.type', 'time_range')
        ->assertJsonPath('data.0.starts_at', '09:00');

    expect($user->workSchedules()->whereDate('effective_from', '2026-06-29')->count())->toBe(1)
        ->and($user->workSchedules()->whereDate('effective_from', '2026-06-29')->first()?->weekday)->toBe(1);
});

test('users can replace weekend work schedules and mark days off', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $this->actingAs($user)
        ->putJson(route('api.me.work-schedules.replace'), [
            'effective_from' => '2026-06-29',
            'schedules' => [
                [
                    'weekday' => 6,
                    'type' => 'day_off',
                ],
                [
                    'weekday' => 7,
                    'type' => 'total_time',
                    'expected_minutes' => 240,
                ],
            ],
        ])
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.weekday', 6)
        ->assertJsonPath('data.0.type', 'day_off')
        ->assertJsonPath('data.0.expected_minutes', 0)
        ->assertJsonPath('data.1.weekday', 7)
        ->assertJsonPath('data.1.type', 'total_time')
        ->assertJsonPath('data.1.expected_minutes', 240);
});

test('authenticated users can list work schedules from a date', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    WorkSchedule::factory()->for($user)->create([
        'weekday' => 1,
        'effective_from' => '2026-06-22',
        'type' => 'total_time',
        'expected_minutes' => 420,
    ]);
    WorkSchedule::factory()->for($user)->create([
        'weekday' => 1,
        'effective_from' => '2026-06-29',
        'type' => 'total_time',
        'expected_minutes' => 480,
    ]);

    $this->actingAs($user)
        ->getJson(route('api.me.work-schedules.index', [
            'from' => '2026-06-29',
        ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.effective_from', '2026-06-29')
        ->assertJsonPath('data.0.expected_minutes', 480);
});

test('time range work schedules require both bounds', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $this->actingAs($user)
        ->putJson(route('api.me.work-schedules.replace'), [
            'effective_from' => '2026-06-29',
            'schedules' => [
                [
                    'weekday' => 1,
                    'type' => 'time_range',
                    'starts_at' => '09:00',
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('schedules.0.ends_at');
});

test('total time work schedules require expected minutes', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $this->actingAs($user)
        ->putJson(route('api.me.work-schedules.replace'), [
            'effective_from' => '2026-06-29',
            'schedules' => [
                [
                    'weekday' => 1,
                    'type' => 'total_time',
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('schedules.0.expected_minutes');
});

test('read endpoints are rate limited', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    for ($attempt = 0; $attempt < 250; $attempt++) {
        $this->getJson(route('api.me.current-shift-state'))
            ->assertOk();
    }

    $this->getJson(route('api.me.current-shift-state'))
        ->assertTooManyRequests()
        ->assertJsonPath('message', 'Too many requests.');
});
