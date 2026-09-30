<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Models\DailyWorkSchedule;
use App\Models\Holiday;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class CreateDailyWorkScheduleSnapshot
{
    public function __construct(
        private FindEffectiveWorkScheduleForDate $getEffectiveWorkScheduleForDate
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

        $followedHoliday = Holiday::query()
            ->whereDate('date', $referenceDate)
            ->whereHas('users', fn ($query) => $query->where('users.id', $user->id))
            ->first();

        $workSchedule = ($this->getEffectiveWorkScheduleForDate)($user, $referenceDate)
            ?? $this->findEarliestWorkScheduleForWeekday($user, $referenceDate);

        if ($followedHoliday === null && $workSchedule === null) {
            return null;
        }

        return DB::transaction(function () use ($followedHoliday, $referenceDate, $user, $workSchedule): DailyWorkSchedule {
            return DailyWorkSchedule::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $referenceDate,
                ],
                $followedHoliday !== null
                    ? [
                        'work_schedule_id' => null,
                        'weekday' => $referenceDate->dayOfWeekIso,
                        'type' => WorkScheduleType::dayOff,
                        'expected_minutes' => 0,
                        'starts_at' => null,
                        'ends_at' => null,
                    ]
                    : [
                        'work_schedule_id' => $workSchedule?->id,
                        'weekday' => $referenceDate->dayOfWeekIso,
                        'type' => $workSchedule?->type,
                        'expected_minutes' => $workSchedule?->expected_minutes,
                        'starts_at' => $workSchedule?->starts_at,
                        'ends_at' => $workSchedule?->ends_at,
                    ],
            )->fresh();
        });
    }

    private function findEarliestWorkScheduleForWeekday(User $user, CarbonImmutable $date): ?WorkSchedule
    {
        return WorkSchedule::query()
            ->where('user_id', $user->id)
            ->where('weekday', $date->dayOfWeekIso)
            ->orderBy('effective_from')
            ->first();
    }
}
