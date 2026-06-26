<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(LazilyRefreshDatabase::class);

test('hours tracker tables and columns exist', function () {
    expect(Schema::hasTable('work_schedules'))->toBeTrue();
    expect(Schema::hasTable('daily_work_schedules'))->toBeTrue();
    expect(Schema::hasTable('shifts'))->toBeTrue();

    expect(Schema::hasColumns('users', ['timezone']))->toBeTrue();

    expect(Schema::hasColumns('work_schedules', [
        'user_id',
        'weekday',
        'type',
        'expected_minutes',
        'starts_at',
        'ends_at',
        'effective_from',
    ]))->toBeTrue();

    expect(Schema::hasColumns('daily_work_schedules', [
        'user_id',
        'work_schedule_id',
        'date',
        'weekday',
        'type',
        'expected_minutes',
        'starts_at',
        'ends_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('shifts', [
        'user_id',
        'started_at',
        'ended_at',
    ]))->toBeTrue();
});
