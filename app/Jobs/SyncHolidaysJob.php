<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Countries\DTOs\CountryData;
use App\Domain\Countries\Repositories\CountriesRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

final class SyncHolidaysJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public ?int $year = null,
    ) {}

    public function handle(CountriesRepository $countriesRepository): void
    {
        $year = $this->year ?? now()->year;

        $countriesRepository->getAllCountries()
            ->each(fn (CountryData $country) => SyncCountryHolidaysJob::dispatch($country->code, $year));
    }
}
