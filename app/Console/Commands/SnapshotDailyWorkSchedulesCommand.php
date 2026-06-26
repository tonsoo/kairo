<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\WorkSchedule\Actions\SnapshotDailyWorkSchedulesAtCurrentLocalMidnight;
use App\Domain\WorkSchedule\Actions\SnapshotDailyWorkSchedulesForDate;
use App\Support\Parsing\DateParser;
use Illuminate\Console\Command;

final class SnapshotDailyWorkSchedulesCommand extends Command
{
    protected $signature = 'hours-tracker:snapshot-daily-work-schedules {--date= : Local date in Y-m-d format}';

    protected $description = 'Create daily work schedule snapshots for users.';

    public function handle(
        SnapshotDailyWorkSchedulesAtCurrentLocalMidnight $snapshotDailyWorkSchedulesAtCurrentLocalMidnight,
        SnapshotDailyWorkSchedulesForDate $snapshotDailyWorkSchedulesForDate,
    ): int {
        $date = $this->option('date');

        if (! is_string($date) || $date === '') {
            $snapshotUsers = ($snapshotDailyWorkSchedulesAtCurrentLocalMidnight)();
            $this->info(sprintf('Snapshotted %d users.', $snapshotUsers));

            return self::SUCCESS;
        }

        $snapshotDate = DateParser::parseLocalDate($date, 'UTC', 'date');
        $snapshotUsers = ($snapshotDailyWorkSchedulesForDate)($snapshotDate);

        $this->info(sprintf('Snapshotted %d users.', $snapshotUsers));

        return self::SUCCESS;
    }
}
