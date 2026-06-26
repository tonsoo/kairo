<?php

declare(strict_types=1);

namespace App\Domain\WorkSchedule\DTOs;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ReplaceWorkSchedulesData
{
    /**
     * @param  Collection<int, ReplaceWorkScheduleEntryData>  $schedules
     */
    public function __construct(
        public CarbonImmutable $effectiveFrom,
        public Collection $schedules,
    ) {}

    /**
     * @return Collection<int, int>
     */
    public function weekdays(): Collection
    {
        return $this->schedules->map(
            fn (ReplaceWorkScheduleEntryData $schedule) => $schedule->weekday,
        );
    }

    /**
     * @return Collection<int, WorkScheduleData>
     */
    public function toWorkScheduleData(User $user): Collection
    {
        $effectiveFrom = $this->effectiveFrom;

        return $this->schedules->map(
            fn (ReplaceWorkScheduleEntryData $schedule) => $schedule->toWorkScheduleData($user, $effectiveFrom),
        );
    }
}
