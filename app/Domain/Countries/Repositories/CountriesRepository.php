<?php

namespace App\Domain\Countries\Repositories;

use App\Domain\Countries\DTOs\CountryData;
use Illuminate\Support\Collection;

interface CountriesRepository
{
    /**
     * @return Collection<CountryData>
     */
    public function getAllCountries(): Collection;
}
