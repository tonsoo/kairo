<?php

declare(strict_types=1);

namespace App\Data\Countries\Repositories;

final readonly class ApiCountriesCountryRepository extends CommonApiAndDevCountryRepository
{
    protected const string url = 'https://www.apicountries.com/countries';

    protected const string cacheKey = 'apicountries.com';

    protected const string providerName = 'ApiCountries';
}
