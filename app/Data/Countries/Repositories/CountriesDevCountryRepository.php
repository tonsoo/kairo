<?php

namespace App\Data\Countries\Repositories;

final readonly class CountriesDevCountryRepository extends CommonApiAndDevCountryRepository
{
    protected const string url = 'https://countries.dev/countries';

    protected const string cacheKey = 'countries.dev';

    protected const string providerName = 'Countries.dev';

    /**
     * @return array<string, mixed>
     */
    protected function query(): array
    {
        return [
            'fields' => 'nativeName,alpha2Code',
            'full' => 'true',
            'sort' => 'nativeName',
            'offset' => 0,
        ];
    }
}
