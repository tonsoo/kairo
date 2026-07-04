<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users can register with a timezone', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'timezone@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'America/Sao_Paulo',
    ]);

    expect(User::query()->where('email', 'timezone@example.com')->first()?->timezone)
        ->toBe('America/Sao_Paulo');
});
