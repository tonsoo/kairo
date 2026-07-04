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
        ->assertInertia(fn (Assert $page) => $page->component('Welcome'));
});

test('homepage exposes portuguese locale in its localized url', function () {
    $this->withoutVite();

    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);

    $this->get($localizedUrlGenerator->url('home', 'pt-BR', absolute: false))
        ->assertOk()
        ->assertSee('<html lang="pt-BR"', false)
        ->assertSee('"locale":"pt-BR"', false)
        ->assertInertia(fn (Assert $page) => $page->component('Welcome'));
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
