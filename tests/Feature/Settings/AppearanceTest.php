<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(LazilyRefreshDatabase::class);

test('appearance page renders light mode without the dark html class', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withUnencryptedCookies(['appearance' => 'light'])
        ->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee("const appearance = 'light';", false)
        ->assertDontSee('class="dark"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Appearance'));
});

test('appearance page renders dark mode with the dark html class', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withUnencryptedCookies(['appearance' => 'dark'])
        ->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee("const appearance = 'dark';", false)
        ->assertSee('class="dark"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Appearance'));
});
