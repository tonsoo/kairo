<?php

use App\Domain\Dashboard\Actions\ListDashboardRelevantWorkSchedulesForPeriod;
use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('shiftExportFormats', 3)
            ->where('shiftExportFormats.0.key', 'csv')
            ->where('shiftExportFormats.1.key', 'xlsx')
            ->where('shiftExportFormats.2.key', 'pdf'));
});

test('authenticated users can visit the history page', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('History')
            ->has('shiftExportFormats', 3)
            ->where('shiftExportFormats.0.key', 'csv')
            ->where('shiftExportFormats.1.key', 'xlsx')
            ->where('shiftExportFormats.2.key', 'pdf'));
});

test('dashboard relevant work schedules are grouped by weekday with historical ordering', function () {
    $user = User::factory()->create();

    $firstMondaySchedule = WorkSchedule::factory()->for($user)->create([
        'weekday' => 1,
        'type' => WorkScheduleType::totalTime,
        'expected_minutes' => 420,
        'effective_from' => '2026-06-01',
    ]);
    $latestMondaySchedule = WorkSchedule::factory()->for($user)->create([
        'weekday' => 1,
        'type' => WorkScheduleType::totalTime,
        'expected_minutes' => 480,
        'effective_from' => '2026-06-15',
    ]);
    $wednesdaySchedule = WorkSchedule::factory()->for($user)->create([
        'weekday' => 3,
        'type' => WorkScheduleType::dayOff,
        'expected_minutes' => 0,
        'effective_from' => '2026-06-10',
    ]);
    $futureMondaySchedule = WorkSchedule::factory()->for($user)->create([
        'weekday' => 1,
        'type' => WorkScheduleType::totalTime,
        'expected_minutes' => 300,
        'effective_from' => '2026-07-01',
    ]);

    $groupedSchedules = app(ListDashboardRelevantWorkSchedulesForPeriod::class)(
        $user,
        CarbonImmutable::parse('2026-06-30', 'UTC'),
    );

    expect($groupedSchedules->keys()->all())->toBe([1, 3])
        ->and($groupedSchedules->get(1)?->modelKeys())->toBe([$firstMondaySchedule->id, $latestMondaySchedule->id])
        ->and($groupedSchedules->get(3)?->modelKeys())->toBe([$wednesdaySchedule->id])
        ->and($groupedSchedules->get(1)?->contains(fn (WorkSchedule $schedule): bool => $schedule->is($futureMondaySchedule)))->toBeFalse();
});
