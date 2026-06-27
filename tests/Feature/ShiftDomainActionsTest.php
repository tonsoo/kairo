<?php

declare(strict_types=1);

use App\Domain\Shift\Actions\ContinueShift;
use App\Domain\Shift\Actions\DeleteShift;
use App\Domain\Shift\Actions\EndShift;
use App\Domain\Shift\Actions\ResolveCurrentShiftState;
use App\Domain\Shift\Actions\RemoveShiftBreak;
use App\Domain\Shift\Actions\StartShift;
use App\Domain\Shift\Actions\UpdateShift;
use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Domain\Shift\Enums\CurrentShiftAction;
use App\Domain\Shift\Exceptions\InvalidShiftBreakRemoval;
use App\Domain\Shift\Exceptions\NoOngoingShiftFound;
use App\Domain\Shift\Exceptions\OngoingShiftAlreadyExists;
use App\Domain\Shift\Exceptions\ShiftOverlapDetected;
use App\Domain\Shift\Exceptions\ShiftOwnershipDenied;
use App\Models\DailyWorkSchedule;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('start shift stores the started instant in utc', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    $shift = app(StartShift::class)($user, CarbonImmutable::parse('2026-06-25 09:00', 'America/Sao_Paulo'));

    expect($shift->ended_at)->toBeNull()
        ->and($shift->started_at->utc()->format('Y-m-d H:i:s'))->toBe('2026-06-25 12:00:00');

    $this->assertModelExists($shift);
});

test('start shift does not create the daily work schedule snapshot', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    app(StartShift::class)($user, CarbonImmutable::parse('2026-06-25 09:00:00', 'America/Sao_Paulo'));

    expect(DailyWorkSchedule::query()
        ->where('user_id', $user->id)
        ->whereDate('date', '2026-06-25')
        ->exists())->toBeFalse();
});

test('cannot start a second ongoing shift for the same user', function () {
    $user = User::factory()->create();

    app(StartShift::class)($user, CarbonImmutable::parse('2026-06-25 09:00', 'UTC'));

    expect(fn () => app(StartShift::class)($user, CarbonImmutable::parse('2026-06-25 10:00', 'UTC')))
        ->toThrow(OngoingShiftAlreadyExists::class);
});

test('end shift closes the latest ongoing shift', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);
    $shift = Shift::factory()->ongoing()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
    ]);

    $endedShift = app(EndShift::class)($user, CarbonImmutable::parse('2026-06-25 18:00', 'America/Sao_Paulo'));

    expect($endedShift->is($shift))->toBeTrue()
        ->and($endedShift->ended_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-06-25 21:00:00');
});

test('ending a shift without an ongoing entry fails', function () {
    $user = User::factory()->create();

    expect(fn () => app(EndShift::class)($user, CarbonImmutable::parse('2026-06-25 18:00', 'UTC')))
        ->toThrow(NoOngoingShiftFound::class);
});

test('continue shift starts a new shift after a completed shift on the same day', function () {
    $user = User::factory()->create([
        'timezone' => 'America/Sao_Paulo',
    ]);

    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
        'ended_at' => '2026-06-25 15:00:00',
    ]);

    $shift = app(ContinueShift::class)($user, CarbonImmutable::parse('2026-06-25 13:30', 'America/Sao_Paulo'));

    expect($shift->started_at->utc()->format('Y-m-d H:i:s'))->toBe('2026-06-25 16:30:00')
        ->and($shift->ended_at)->toBeNull();
});

test('update shift prevents overlaps with other shifts', function () {
    $user = User::factory()->create();
    $shiftToUpdate = Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
        'ended_at' => '2026-06-25 14:00:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 15:00:00',
        'ended_at' => '2026-06-25 17:00:00',
    ]);

    $period = new ShiftPeriodData(
        CarbonImmutable::parse('2026-06-25 14:30', 'UTC'),
        CarbonImmutable::parse('2026-06-25 16:30', 'UTC'),
    );

    expect(fn () => app(UpdateShift::class)($user, $shiftToUpdate, $period))
        ->toThrow(ShiftOverlapDetected::class);
});

test('remove shift break merges the selected shifts', function () {
    $user = User::factory()->create();
    $previousShift = Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
        'ended_at' => '2026-06-25 15:00:00',
    ]);
    $nextShift = Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 16:00:00',
        'ended_at' => '2026-06-25 20:00:00',
    ]);

    $mergedShift = app(RemoveShiftBreak::class)($user, $previousShift, $nextShift);

    expect($mergedShift->id)->toBe($previousShift->id)
        ->and($mergedShift->started_at->utc()->format('Y-m-d H:i:s'))->toBe('2026-06-25 12:00:00')
        ->and($mergedShift->ended_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-06-25 20:00:00');

    $this->assertModelExists($mergedShift);
    $this->assertModelMissing($nextShift);
});

test('remove shift break requires the previous shift to be completed', function () {
    $user = User::factory()->create();
    $previousShift = Shift::factory()->ongoing()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
        'ended_at' => null,
    ]);
    $nextShift = Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 16:00:00',
        'ended_at' => '2026-06-25 20:00:00',
    ]);

    expect(fn () => app(RemoveShiftBreak::class)($user, $previousShift, $nextShift))
        ->toThrow(InvalidShiftBreakRemoval::class);
});

test('delete shift enforces ownership', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $shift = Shift::factory()->for($owner)->create();

    expect(fn () => app(DeleteShift::class)($intruder, $shift))
        ->toThrow(ShiftOwnershipDenied::class);

    app(DeleteShift::class)($owner, $shift);

    $this->assertModelMissing($shift);
});

test('current shift state resolves start end and continue actions', function () {
    $user = User::factory()->create();

    $startState = app(ResolveCurrentShiftState::class)($user, CarbonImmutable::parse('2026-06-25 08:00', 'America/Sao_Paulo'));

    expect($startState->nextAction)->toBe(CurrentShiftAction::start);

    Shift::factory()->ongoing()->for($user)->create([
        'started_at' => '2026-06-25 01:00:00',
        'ended_at' => null,
    ]);

    $endState = app(ResolveCurrentShiftState::class)($user, CarbonImmutable::parse('2026-06-25 08:00', 'America/Sao_Paulo'));

    expect($endState->nextAction)->toBe(CurrentShiftAction::end)
        ->and($endState->hasOngoingShift)->toBeTrue();

    Shift::query()->where('user_id', $user->id)->delete();

    Shift::factory()->for($user)->create([
        'started_at' => '2026-06-25 12:00:00',
        'ended_at' => '2026-06-25 15:00:00',
    ]);

    $continueState = app(ResolveCurrentShiftState::class)($user, CarbonImmutable::parse('2026-06-25 14:00', 'America/Sao_Paulo'));

    expect($continueState->nextAction)->toBe(CurrentShiftAction::continue)
        ->and($continueState->hasShiftToday)->toBeTrue();
});
