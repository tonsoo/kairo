<?php

namespace App\Data\Holidays\Repositories;

use App\Domain\Holidays\DTOs\HolidayData;
use App\Domain\Holidays\Repositories\HolidaysRepository;
use App\Traits\HasRepositoryFallback;
use Illuminate\Support\Collection;
use Throwable;

final readonly class FallbackHolidayRepository implements HolidaysRepository
{
    use HasRepositoryFallback;

    /**
     * @param  array<int, HolidaysRepository>  $repositories
     */
    public function __construct(
        private array $repositories,
    ) {}

    /**
     * @return Collection<int, HolidayData>
     *
     * @throws Throwable
     */
    public function getHolidaysForCountryCode(string $countryCode, int $year): Collection
    {
        return $this->fallback(
            repositories: $this->repositories,
            callback: fn (HolidaysRepository $repository) => $repository->getHolidaysForCountryCode($countryCode, $year),
            isValidResult: fn (Collection $holidays) => $holidays->isNotEmpty(),
        );
    }

    /**
     * @throws Throwable
     */
    public function getHolidayForCountryCode(string $countryCode): ?HolidayData
    {
        return $this->fallback(
            repositories: $this->repositories,
            callback: fn (HolidaysRepository $repository) => $repository->getHolidayForCountryCode($countryCode),
            isValidResult: fn (?HolidayData $holiday) => $holiday !== null,
        );
    }
}
