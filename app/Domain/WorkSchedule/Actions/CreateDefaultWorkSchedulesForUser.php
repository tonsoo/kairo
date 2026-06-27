<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Domain\WorkSchedule\DTOs\WorkScheduleData;
use App\Models\User;
use App\Support\Parsing\DateParser;
use Illuminate\Support\Facades\DB;

final readonly class CreateDefaultWorkSchedulesForUser
{
    public function __construct(
        private UpsertWorkSchedule $upsertWorkSchedule,
    ) {}

    public function __invoke(User $user): void
    {
        $effectiveFrom = DateParser::nowInTimezone($user->timezone)->startOfDay();

        DB::transaction(function () use ($effectiveFrom, $user): void {
            foreach (range(1, 5) as $weekday) {
                ($this->upsertWorkSchedule)($user, WorkScheduleData::totalTime(
                    $weekday,
                    8 * 60,
                    $effectiveFrom->setTime(9, 0),
                ));
            }

            foreach ([6, 7] as $weekday) {
                ($this->upsertWorkSchedule)($user, WorkScheduleData::dayOff(
                    $weekday,
                    $effectiveFrom,
                ));
            }
        });
    }
}
