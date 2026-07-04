<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Localization\LocalizedUrlGenerator;
use Inertia\Testing\AssertableInertia as Assert;

test('the root path redirects to the default localized home route', function () {
    $this->get('/')
        ->assertRedirect(route('home', absolute: false));
});

test('the selected locale changes the html lang attribute', function () {
    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);

    $response = $this->get($localizedUrlGenerator->url('home', 'pt-BR', absolute: false));

    $response->assertOk();
    $response->assertSee('lang="pt-BR"', false);
});

test('the selected locale applies to authenticated panel pages', function () {
    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get($localizedUrlGenerator->url('dashboard', 'pt-BR', absolute: false));

    $response->assertOk();
    $response->assertSee('lang="pt-BR"', false);
});

test('the selected locale is shared with inertia pages without sending a translation payload', function () {
    $this->withoutVite();

    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get($localizedUrlGenerator->url('dashboard', 'pt-BR', absolute: false))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'pt-BR')
            ->where('localeOptions.0', [
                'code' => 'en',
                'url' => $localizedUrlGenerator->url('dashboard', 'en'),
            ])
            ->where('localeOptions.1', [
                'code' => 'pt-BR',
                'url' => $localizedUrlGenerator->url('dashboard', 'pt-BR'),
            ])
            ->missing('translations'));
});

test('the selected locale is shared with the public home inertia page without sending a translation payload', function () {
    $this->withoutVite();

    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);

    $this->get($localizedUrlGenerator->url('home', 'pt-BR', absolute: false))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'pt-BR')
            ->where('localeOptions.0', [
                'code' => 'en',
                'url' => $localizedUrlGenerator->url('home', 'en'),
            ])
            ->where('localeOptions.1', [
                'code' => 'pt-BR',
                'url' => $localizedUrlGenerator->url('home', 'pt-BR'),
            ])
            ->missing('translations'));
});

test('localized routes keep their original path names', function () {
    $this->get('/pt-BR/dashboard')->assertRedirect(route('login', ['locale' => 'pt-BR'], false));
});
