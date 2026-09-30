<?php

use App\Domain\Countries\DTOs\CountryData;
use App\Domain\Countries\Repositories\CountriesRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    app()->instance(CountriesRepository::class, new class implements CountriesRepository
    {
        public function getAllCountries(): Collection
        {
            return collect([
                new CountryData('Brazil', 'BR'),
                new CountryData('United States', 'US'),
            ]);
        }
    });
});

test('guests are redirected away from the holidays page', function () {
    $this->get(route('holidays'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the holidays page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('holidays'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Holidays')
            ->has('countries', 2)
            ->where('initialCountryCode', 'BR'),
        );
});
