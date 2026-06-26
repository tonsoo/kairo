<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Models\DailyWorkSchedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class BuildDailyWorkScheduleSnapshot
{
    public function __construct(
        private GetEffectiveWorkScheduleForDate $getEffectiveWorkScheduleForDate
    ) {}

    public function __invoke(User $user, CarbonImmutable $date): ?DailyWorkSchedule
    {
        $referenceDate = $date->startOfDay();
        $existingDailyWorkSchedule = DailyWorkSchedule::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $referenceDate)
            ->first();

        if ($existingDailyWorkSchedule !== null) {
            return $existingDailyWorkSchedule;
        }

        $workSchedule = ($this->getEffectiveWorkScheduleForDate)($user, $referenceDate);

        if ($workSchedule === null) {
            return null;
        }

        return DB::transaction(function () use ($referenceDate, $user, $workSchedule): DailyWorkSchedule {
            return DailyWorkSchedule::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $referenceDate,
                ],
                [
                    'work_schedule_id' => $workSchedule->id,
                    'weekday' => $referenceDate->dayOfWeekIso,
                    'type' => $workSchedule->type,
                    'expected_minutes' => $workSchedule->expected_minutes,
                    'starts_at' => $workSchedule->starts_at,
                    'ends_at' => $workSchedule->ends_at,
                ],
            )->fresh();
        });
    }
}
