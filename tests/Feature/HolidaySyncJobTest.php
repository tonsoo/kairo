<?php

use App\Domain\Countries\DTOs\CountryData;
use App\Domain\Countries\Repositories\CountriesRepository;
use App\Domain\Holidays\DTOs\HolidayData;
use App\Domain\Holidays\Repositories\HolidaysRepository;
use App\Jobs\SyncCountryHolidaysJob;
use App\Jobs\SyncHolidaysJob;
use App\Models\Holiday;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;

uses(LazilyRefreshDatabase::class);

test('sync country holidays job upserts holidays for a country and year', function () {
    app()->instance(HolidaysRepository::class, new class implements HolidaysRepository
    {
        public function getHolidaysForCountryCode(string $countryCode, int $year): Collection
        {
            expect($countryCode)->toBe('BR')
                ->and($year)->toBe(2026);

            return collect([
                new HolidayData('Christmas Day', CarbonImmutable::parse('2026-12-25', 'UTC'), true),
                new HolidayData('Sao Paulo Anniversary', CarbonImmutable::parse('2026-01-25', 'UTC'), false),
            ]);
        }

        public function getHolidayForCountryCode(string $countryCode): ?HolidayData
        {
            return null;
        }
    });

    Holiday::factory()->create([
        'date' => '2026-12-25',
        'country_code' => 'BR',
        'name' => 'Christmas Day',
        'is_national' => false,
    ]);

    app(SyncCountryHolidaysJob::class, ['countryCode' => 'BR', 'year' => 2026])->handle(app(HolidaysRepository::class));

    expect(Holiday::query()
        ->where('country_code', 'BR')
        ->orderBy('date')
        ->pluck('is_national', 'name')
        ->all())
        ->toBe([
            'Sao Paulo Anniversary' => false,
            'Christmas Day' => true,
        ]);
});

test('sync holidays job dispatches one country job per available country', function () {
    Queue::fake();

    app()->instance(CountriesRepository::class, new class implements CountriesRepository
    {
        public function getAllCountries(): Collection
        {
            return collect([
                new CountryData('Brazil', 'BR'),
                new CountryData('United States', 'US'),
            ]);
        }
    });

    app(SyncHolidaysJob::class, ['year' => 2027])->handle(app(CountriesRepository::class));

    Queue::assertPushed(SyncCountryHolidaysJob::class, 2);
    Queue::assertPushed(SyncCountryHolidaysJob::class, fn (SyncCountryHolidaysJob $job) => $job->countryCode === 'BR' && $job->year === 2027);
    Queue::assertPushed(SyncCountryHolidaysJob::class, fn (SyncCountryHolidaysJob $job) => $job->countryCode === 'US' && $job->year === 2027);
});
