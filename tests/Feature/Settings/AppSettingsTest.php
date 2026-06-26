<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('app settings page is displayed', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    $this->actingAs($user)
        ->get(route('app-settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/App')
            ->where('timezone', 'UTC')
            ->where('timezones', fn (mixed $timezones) => collect($timezones)->contains('UTC')),
        );
});

test('preferred timezone can be updated', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    $this->actingAs($user)
        ->patch(route('app-settings.update'), [
            'timezone' => 'America/Sao_Paulo',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('app-settings.edit'));

    expect($user->refresh()->timezone)->toBe('America/Sao_Paulo');
});

test('preferred timezone must be valid', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    $this->actingAs($user)
        ->from(route('app-settings.edit'))
        ->patch(route('app-settings.update'), [
            'timezone' => 'Mars/Olympus',
        ])
        ->assertSessionHasErrors('timezone')
        ->assertRedirect(route('app-settings.edit'));

    expect($user->refresh()->timezone)->toBe('UTC');
});
