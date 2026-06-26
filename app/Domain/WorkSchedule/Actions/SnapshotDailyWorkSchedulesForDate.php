<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

final readonly class SnapshotDailyWorkSchedulesForDate
{
    public function __construct(
        private BuildDailyWorkScheduleSnapshot $buildDailyWorkScheduleSnapshot,
    ) {}

    public function __invoke(CarbonImmutable $date): int
    {
        $snapshotDate = $date->startOfDay();
        $snapshotUsers = 0;

        User::query()
            ->select('id', 'timezone')
            ->orderBy('id')
            ->chunkById(100, function (Collection $users) use (&$snapshotUsers, $snapshotDate): void {
                foreach ($users as $user) {
                    if (($this->buildDailyWorkScheduleSnapshot)($user, $snapshotDate) !== null) {
                        $snapshotUsers++;
                    }
                }
            });

        return $snapshotUsers;
    }
}
