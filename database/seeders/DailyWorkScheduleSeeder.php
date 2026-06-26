<?php

namespace Database\Seeders;

use App\Models\DailyWorkSchedule;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyWorkScheduleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create();
        $effectiveFrom = today()->startOfMonth()->toDateString();
        $startOfWeek = CarbonImmutable::today()->startOfWeek(CarbonInterface::MONDAY);

        $workSchedules = WorkSchedule::factory()
            ->count(5)
            ->timeRange()
            ->for($user)
            ->sequence(
                ['weekday' => 1, 'effective_from' => $effectiveFrom],
                ['weekday' => 2, 'effective_from' => $effectiveFrom],
                ['weekday' => 3, 'effective_from' => $effectiveFrom],
                ['weekday' => 4, 'effective_from' => $effectiveFrom],
                ['weekday' => 5, 'effective_from' => $effectiveFrom],
            )
            ->create();

        $workSchedules->each(function (WorkSchedule $workSchedule) use ($startOfWeek): void {
            DailyWorkSchedule::factory()
                ->fromWorkSchedule($workSchedule, $startOfWeek->addDays($workSchedule->weekday - 1))
                ->create();
        });
    }
}
