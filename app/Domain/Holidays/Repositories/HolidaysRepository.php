<?php

namespace App\Domain\Holidays\Repositories;

use App\Domain\Holidays\DTOs\HolidayData;
use Illuminate\Support\Collection;

interface HolidaysRepository
{
    /**
     * @return Collection<int, HolidayData>
     */
    public function getHolidaysForCountryCode(string $countryCode, int $year): Collection;

    public function getHolidayForCountryCode(string $countryCode): ?HolidayData;
}
