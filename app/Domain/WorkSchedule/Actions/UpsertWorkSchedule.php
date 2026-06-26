<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Domain\WorkSchedule\DTOs\WorkScheduleData;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Support\Facades\DB;

final readonly class UpsertWorkSchedule
{
    public function __invoke(User $user, WorkScheduleData $workScheduleData): WorkSchedule
    {
        $workSchedule = DB::transaction(
            fn () => WorkSchedule::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'weekday' => $workScheduleData->weekday,
                    'effective_from' => $workScheduleData->effectiveFrom,
                ],
                [
                    'type' => $workScheduleData->type,
                    'expected_minutes' => $workScheduleData->expectedMinutes,
                    'starts_at' => $workScheduleData->startsAt?->format('H:i:s'),
                    'ends_at' => $workScheduleData->endsAt?->format('H:i:s'),
                ],
            )
        );

        return $workSchedule->fresh();
    }
}
