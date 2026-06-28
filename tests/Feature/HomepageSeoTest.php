<?php

declare(strict_types=1);

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

test('homepage exposes portuguese locale when locale cookie is set', function () {
    $this->withoutVite();

    $this->withCookie('locale', 'pt-BR')
        ->get(route('home'))
        ->assertOk()
        ->assertSee('<html lang="pt-BR"', false)
        ->assertSee('"locale":"pt-BR"', false)
        ->assertInertia(fn (Assert $page) => $page->component('Welcome'));
});

test('public seo files use the kairo production domain', function () {
    expect(File::get(public_path('sitemap.xml')))
        ->toContain('https://kairo.alysson-thoaldo.com.br/')
        ->toContain('<lastmod>2026-06-28T00:00:00+00:00</lastmod>');

    expect(File::get(public_path('robots.txt')))
        ->toContain('Sitemap: https://kairo.alysson-thoaldo.com.br/sitemap.xml')
        ->toContain('Disallow: /dashboard');
});
