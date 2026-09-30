<?php

declare(strict_types=1);

namespace App\Data\Holidays\Repositories;

use App\Domain\Holidays\DTOs\HolidayData;
use App\Domain\Holidays\Repositories\HolidaysRepository;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class NagerHolidaysRepository implements HolidaysRepository
{
    private const string url = 'https://date.nager.at/api/v4/Holidays';

    private const string cacheKeyPrefix = 'holidays_repository:date.nager.at:v1:';

    /**
     * @return Collection<int, HolidayData>
     */
    public function getHolidaysForCountryCode(string $countryCode, int $year): Collection
    {
        $countryCode = strtoupper($countryCode);

        /** @var Collection<int, HolidayData> $holidays */
        $holidays = Cache::remember(
            $this->cacheKey($countryCode, $year),
            now()->addDay(),
            fn (): Collection => $this->fetchHolidays($countryCode, $year),
        );

        return $holidays;
    }

    public function getHolidayForCountryCode(string $countryCode): ?HolidayData
    {
        $today = CarbonImmutable::today();
        $year = now()->year;

        /** @var ?HolidayData $holiday */
        $holiday = $this->getHolidaysForCountryCode($countryCode, $year)
            ->filter(fn (HolidayData $holiday) => $holiday->date->greaterThanOrEqualTo($today))
            ->sortBy(fn (HolidayData $holiday) => $holiday->date->toDateString())
            ->first();

        return $holiday;
    }

    /**
     * @return Collection<int, HolidayData>
     *
     * @throws ConnectionException
     */
    private function fetchHolidays(string $countryCode, int $year): Collection
    {
        $response = Http::acceptJson()
            ->timeout(10)
            ->retry(2, 200)
            ->get(sprintf('%s/%s/%d', self::url, $countryCode, $year));

        if ($response->failed()) {
            throw new RuntimeException(sprintf(
                'Nager.Date holidays request failed with status [%d].',
                $response->status(),
            ));
        }

        $holidays = $response->json();

        if (! is_array($holidays)) {
            throw new RuntimeException('Nager.Date returned an invalid holidays response.');
        }

        return collect($holidays)
            ->map(fn (mixed $holiday) => $this->mapHoliday($holiday))
            ->filter()
            ->sortBy(fn (HolidayData $holiday) => $holiday->date->toDateString())
            ->values();
    }

    private function mapHoliday(mixed $holiday): ?HolidayData
    {
        if (! is_array($holiday)) {
            return null;
        }

        $name = $holiday['name'] ?? null;
        $date = $holiday['date'] ?? null;
        $isNational = $holiday['nationalHoliday'] ?? null;

        if (! is_string($name) || ! is_string($date) || ! is_bool($isNational)) {
            return null;
        }

        return new HolidayData(
            name: $name,
            date: CarbonImmutable::parse($date),
            isNational: $isNational,
        );
    }

    private function cacheKey(string $countryCode, int $year): string
    {
        return sprintf('%s.%s.%d', self::cacheKeyPrefix, $countryCode, $year);
    }
}
