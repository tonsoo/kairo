<?php

declare(strict_types=1);

use App\Support\Localization\LocalizedUrlGenerator;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

test('homepage exposes english locale by default', function () {
    $this->withoutVite();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<html lang="en"', false)
        ->assertSee('"locale":"en"', false)
        ->assertSee('<title>Kairo | Time Tracker for Shifts and Work Hours</title>', false)
        ->assertSee('name="description" content="Kairo is a precise time tracker and hours tracker for work hours, shift logs, overtime balance, and daily hour tracking."', false)
        ->assertSee('property="og:title" content="Kairo | Time Tracker for Shifts and Work Hours"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('meta.title', 'Kairo | Time Tracker for Shifts and Work Hours')
            ->where('meta.description', 'Kairo is a precise time tracker and hours tracker for work hours, shift logs, overtime balance, and daily hour tracking.'));
});

test('homepage exposes portuguese locale in its localized url', function () {
    $this->withoutVite();

    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);

    $this->get($localizedUrlGenerator->url('home', 'pt-BR', absolute: false))
        ->assertOk()
        ->assertSee('<html lang="pt-BR"', false)
        ->assertSee('"locale":"pt-BR"', false)
        ->assertSee('<title>Kairo | Controle de Horas e Ponto para Turnos</title>', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('meta.title', 'Kairo | Controle de Horas e Ponto para Turnos')
            ->where('meta.description', 'Kairo e um controle de horas e controle de ponto preciso para turnos, banco de horas, horas extras e rotina de trabalho.'));
});

test('public seo endpoints expose localized sitemap urls and host-agnostic robots metadata', function () {
    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('content-type', 'application/xml; charset=UTF-8')
        ->assertSee($localizedUrlGenerator->url('home', 'en'), false)
        ->assertSee($localizedUrlGenerator->url('home', 'pt-BR'), false)
        ->assertSee('hreflang="en"', false)
        ->assertSee('hreflang="pt-BR"', false);

    expect(File::get(public_path('robots.txt')))
        ->toContain('Sitemap: /sitemap.xml')
        ->toContain('Disallow: /dashboard');
});
