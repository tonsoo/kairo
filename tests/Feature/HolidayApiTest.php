<?php

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('authenticated users can list holidays for a month and country', function () {
    $user = User::factory()->create();
    $followedHoliday = Holiday::factory()->create([
        'date' => '2026-07-09',
        'country_code' => 'BR',
        'is_national' => true,
        'name' => 'Constitutionalist Revolution',
    ]);
    $unfollowedHoliday = Holiday::factory()->create([
        'date' => '2026-07-20',
        'country_code' => 'BR',
        'is_national' => false,
        'name' => 'Regional Celebration',
    ]);
    Holiday::factory()->create([
        'date' => '2026-07-04',
        'country_code' => 'US',
        'name' => 'Independence Day',
    ]);

    $user->holidays()->attach($followedHoliday->id);

    $this->actingAs($user)
        ->getJson(route('api.me.holidays.index', [
            'month' => '2026-07-01',
            'country_code' => 'BR',
        ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $followedHoliday->id)
        ->assertJsonPath('data.0.is_followed', true)
        ->assertJsonPath('data.1.id', $unfollowedHoliday->id)
        ->assertJsonPath('data.1.is_followed', false);
});

test('authenticated users can follow and unfollow holidays', function () {
    $user = User::factory()->create();
    $holiday = Holiday::factory()->create([
        'date' => '2026-12-25',
        'country_code' => 'BR',
        'name' => 'Christmas Day',
    ]);

    $this->actingAs($user)
        ->putJson(route('api.me.holidays.follow', ['holiday' => $holiday]), [
            'followed' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.id', $holiday->id)
        ->assertJsonPath('data.is_followed', true);

    expect($user->holidays()->whereKey($holiday->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->putJson(route('api.me.holidays.follow', ['holiday' => $holiday]), [
            'followed' => false,
        ])
        ->assertOk()
        ->assertJsonPath('data.is_followed', false);

    expect($user->holidays()->whereKey($holiday->id)->exists())->toBeFalse();
});
