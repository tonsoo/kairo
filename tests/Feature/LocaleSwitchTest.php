<?php

declare(strict_types=1);

use App\Models\User;

test('users can switch the active locale', function () {
    $response = $this->from(route('home'))
        ->patch(route('locale.update', ['locale' => 'pt-BR'], absolute: false));

    $response->assertRedirect(route('home', absolute: false));
    $response->assertCookie('locale');
});

test('the selected locale changes the html lang attribute', function () {
    $response = $this->withCookie('locale', 'pt-BR')->get(route('home'));

    $response->assertOk();
    $response->assertSee('lang="pt-BR"', false);
});

test('the selected locale applies to authenticated panel pages', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withCookie('locale', 'pt-BR')
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('lang="pt-BR"', false);
});

test('unsupported locales are not accepted', function () {
    $response = $this->patch('/locale/es');

    $response->assertNotFound();
});
