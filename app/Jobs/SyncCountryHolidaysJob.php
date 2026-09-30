<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Holidays\Repositories\HolidaysRepository;
use App\Models\Holiday;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SyncCountryHolidaysJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(
        public string $countryCode,
        public int $year,
    ) {}

    public function handle(HolidaysRepository $holidaysRepository): void
    {
        $holidays = $holidaysRepository->getHolidaysForCountryCode(
            countryCode: $this->countryCode,
            year: $this->year,
        );

        foreach ($holidays as $holidayData) {
            $holiday = Holiday::query()
                ->where('country_code', strtoupper($this->countryCode))
                ->whereDate('date', $holidayData->date)
                ->where('name', $holidayData->name)
                ->firstOrNew();

            $holiday->forceFill([
                'country_code' => strtoupper($this->countryCode),
                'date' => $holidayData->date->toDateString(),
                'name' => $holidayData->name,
                'is_national' => $holidayData->isNational,
            ])->save();
        }
    }
}
