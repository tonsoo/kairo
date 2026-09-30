<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Domain\Countries\DTOs\CountryData;
use App\Domain\Countries\Repositories\CountriesRepository;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class HolidaysController extends Controller
{
    public function __invoke(CountriesRepository $countriesRepository): Response
    {
        $countries = $countriesRepository->getAllCountries()
            ->sortBy(fn (CountryData $country) => $country->name)
            ->values();

        return Inertia::render('Holidays', [
            'countries' => $countries->map(fn (CountryData $country): array => [
                'code' => strtoupper($country->code),
                'name' => $country->name,
            ]),
            'initialMonth' => now()->startOfMonth()->toDateString(),
            'initialCountryCode' => $countries->first()?->code,
        ]);
    }
}
