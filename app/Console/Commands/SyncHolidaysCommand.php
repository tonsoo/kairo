<?php

namespace App\Console\Commands;

use App\Jobs\SyncCountryHolidaysJob;
use App\Jobs\SyncHolidaysJob;
use Illuminate\Console\Command;

final class SyncHolidaysCommand extends Command
{
    protected $signature = 'kairo:sync-holidays {--year= : Holiday year in Y format} {--country= : Restrict the sync to a single country code}';

    protected $description = 'Dispatch holiday synchronization jobs.';

    public function handle(): int
    {
        $yearOption = $this->option('year');
        $countryOption = $this->option('country');
        $year = is_numeric($yearOption) ? (int) $yearOption : now()->year;

        if (is_string($countryOption) && $countryOption !== '') {
            $countryCode = strtoupper($countryOption);
            SyncCountryHolidaysJob::dispatch($countryCode, $year);
            $this->info(sprintf('Queued holiday sync for %s (%d).', $countryCode, $year));

            return self::SUCCESS;
        }

        SyncHolidaysJob::dispatch($year);
        $this->info(sprintf('Queued holiday sync for all countries (%d).', $year));

        return self::SUCCESS;
    }
}
