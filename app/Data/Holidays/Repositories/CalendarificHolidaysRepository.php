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

final readonly class CalendarificHolidaysRepository implements HolidaysRepository
{
    private const string url = 'https://calendarific.com/api/v2/holidays';

    private const string cacheKeyPrefix = 'holidays_repository:calendarific.com:v1:';

    public function __construct(
        private string $apiKey,
    ) {}

    /**
     * @return Collection<int, HolidayData>
     */
    public function getHolidaysForCountryCode(string $countryCode, int $year): Collection
    {
        if (empty($this->apiKey)) {
            return collect();
        }

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
            ->get(self::url, [
                'api_key' => $this->apiKey,
                'country' => $countryCode,
                'year' => $year,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(sprintf(
                'Calendarific request failed with status [%d].',
                $response->status(),
            ));
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Calendarific returned an invalid response.');
        }

        $holidays = $data['response']['holidays'] ?? null;

        if (! is_array($holidays)) {
            throw new RuntimeException('Calendarific returned an invalid holidays response.');
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
        $date = $holiday['date']['iso'] ?? null;

        if (! is_string($name) || ! is_string($date)) {
            return null;
        }

        return new HolidayData(
            name: $name,
            date: CarbonImmutable::parse($date),
            isNational: $this->isNationalHoliday($holiday),
        );
    }

    /**
     * @param  array<string, mixed>  $holiday
     */
    private function isNationalHoliday(array $holiday): bool
    {
        $primaryType = $holiday['primary_type'] ?? null;

        if (is_string($primaryType) && strcasecmp($primaryType, 'National Holiday') === 0) {
            return true;
        }

        $types = $holiday['type'] ?? null;

        if (! is_array($types)) {
            return false;
        }

        return collect($types)
            ->filter(fn (mixed $type) => is_string($type))
            ->contains(fn (string $type) => strcasecmp($type, 'National holiday') === 0);
    }

    private function cacheKey(string $countryCode, int $year): string
    {
        return sprintf('%s.%s.%d', self::cacheKeyPrefix, $countryCode, $year);
    }
}
