<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\Actions;

use App\Domain\WorkSchedule\DTOs\ReplaceWorkSchedulesData;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class SyncWorkSchedulesForEffectiveFrom
{
    public function __construct(
        private UpsertWorkSchedule $upsertWorkSchedule,
    ) {}

    /**
     * @return Collection<int, WorkSchedule>
     */
    public function __invoke(User $user, ReplaceWorkSchedulesData $replaceWorkSchedulesData): Collection
    {
        $weekdays = $replaceWorkSchedulesData->weekdays()->all();
        $workScheduleData = $replaceWorkSchedulesData->toWorkScheduleData($user);

        return DB::transaction(function () use ($replaceWorkSchedulesData, $user, $weekdays, $workScheduleData) {
            $query = WorkSchedule::query()
                ->where('user_id', $user->id)
                ->whereDate('effective_from', $replaceWorkSchedulesData->effectiveFrom)
                ->when(
                    ! blank($weekdays),
                    fn ($query) => $query->whereNotIn('weekday', $weekdays),
                );

            $query->delete();

            foreach ($workScheduleData as $data) {
                ($this->upsertWorkSchedule)($user, $data);
            }

            return WorkSchedule::query()
                ->where('user_id', $user->id)
                ->whereDate('effective_from', $replaceWorkSchedulesData->effectiveFrom)
                ->orderBy('weekday')
                ->get();
        });
    }
}
