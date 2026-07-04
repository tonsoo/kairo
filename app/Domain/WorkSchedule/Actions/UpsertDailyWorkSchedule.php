<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Domain\WorkSchedule\DTOs\WorkScheduleData;
use App\Models\DailyWorkSchedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class UpsertDailyWorkSchedule
{
    public function __invoke(User $user, CarbonImmutable $date, WorkScheduleData $data): DailyWorkSchedule
    {
        /** @var DailyWorkSchedule $dailyWorkSchedule */
        $dailyWorkSchedule = DB::transaction(function () use ($data, $date, $user): DailyWorkSchedule {
            $dailyWorkSchedule = DailyWorkSchedule::query()->firstOrNew([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
            ]);

            $dailyWorkSchedule->forceFill([
                'work_schedule_id' => null,
                'weekday' => $data->weekday,
                'type' => $data->type,
                'expected_minutes' => $data->expectedMinutes,
                'starts_at' => $data->startsAt?->format('H:i:s'),
                'ends_at' => $data->endsAt?->format('H:i:s'),
            ])->save();

            return $dailyWorkSchedule;
        });

        return $dailyWorkSchedule->fresh();
    }
}
