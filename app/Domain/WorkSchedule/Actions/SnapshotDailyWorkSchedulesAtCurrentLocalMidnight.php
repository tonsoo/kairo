<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

final readonly class SnapshotDailyWorkSchedulesAtCurrentLocalMidnight
{
    public function __construct(
        private BuildDailyWorkScheduleSnapshot $buildDailyWorkScheduleSnapshot,
    ) {}

    public function __invoke(?CarbonImmutable $referenceMoment = null): int
    {
        $currentMoment = $referenceMoment ?? CarbonImmutable::now('UTC');
        $snapshotUsers = 0;

        User::query()
            ->select('id', 'timezone')
            ->orderBy('id')
            ->chunkById(100, function (Collection $users) use (&$snapshotUsers, $currentMoment): void {
                foreach ($users as $user) {
                    $localMoment = $currentMoment->setTimezone($user->timezone);

                    if ($localMoment->hour !== 0) {
                        continue;
                    }

                    if (($this->buildDailyWorkScheduleSnapshot)($user, $localMoment->startOfDay()) !== null) {
                        $snapshotUsers++;
                    }
                }
            });

        return $snapshotUsers;
    }
}
