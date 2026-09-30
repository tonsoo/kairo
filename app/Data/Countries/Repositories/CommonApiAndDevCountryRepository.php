<?php

declare(strict_types=1);

namespace App\Data\Countries\Repositories;

use App\Domain\Countries\DTOs\CountryData;
use App\Domain\Countries\Repositories\CountriesRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract readonly class CommonApiAndDevCountryRepository implements CountriesRepository
{
    protected const string url = '';

    protected const string cacheKey = '';

    protected const string providerName = 'Countries provider';

    public function getAllCountries(): Collection
    {
        return Cache::remember(
            static::cacheKey,
            now()->addDay(),
            fn (): Collection => $this->fetchCountries(),
        );
    }

    /**
     * @return Collection<int, CountryData>
     *
     * @throws ConnectionException
     */
    private function fetchCountries(): Collection
    {
        $response = Http::acceptJson()
            ->timeout(10)
            ->retry(2, 200)
            ->get(static::url, $this->query());

        if ($response->failed()) {
            throw new RuntimeException(sprintf(
                '%s request failed with status [%d].',
                static::providerName,
                $response->status(),
            ));
        }

        $countries = $response->json();

        if (! is_array($countries)) {
            throw new RuntimeException(sprintf(
                '%s returned an invalid response.',
                static::providerName,
            ));
        }

        return collect($countries)
            ->map(fn (mixed $country) => $this->mapCountry($country))
            ->filter()
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected function query(): array
    {
        return [];
    }

    private function mapCountry(mixed $country): ?CountryData
    {
        if (! is_array($country)) {
            return null;
        }

        $name = $country['nativeName'] ?? $country['name'] ?? null;
        $code = $country['alpha2Code'] ?? null;

        if (! is_string($name) || ! is_string($code)) {
            return null;
        }

        return new CountryData(
            name: $name,
            code: $code,
        );
    }
}
