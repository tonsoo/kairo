<?php

namespace App\Data\Countries\Repositories;

use App\Domain\Countries\Repositories\CountriesRepository;
use App\Traits\HasRepositoryFallback;
use Illuminate\Support\Collection;
use Throwable;

final readonly class FallbackCountryRepository implements CountriesRepository
{
    use HasRepositoryFallback;

    /**
     * @param  array<int, CountriesRepository>  $repositories
     */
    public function __construct(
        private array $repositories,
    ) {}

    /**
     * @throws Throwable
     */
    public function getAllCountries(): Collection
    {
        return $this->fallback(
            repositories: $this->repositories,
            callback: fn (CountriesRepository $repository) => $repository->getAllCountries(),
            isValidResult: fn (Collection $countries) => $countries->isNotEmpty(),
        ) ?? collect();
    }
}
