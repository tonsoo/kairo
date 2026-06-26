<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

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
            ->has('shiftExportFormats', 1)
            ->where('shiftExportFormats.0.key', 'csv'));
});

test('authenticated users can visit the history page', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('History')
            ->has('shiftExportFormats', 1)
            ->where('shiftExportFormats.0.key', 'csv'));
});
