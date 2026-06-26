<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create();
        $effectiveFrom = today()->startOfMonth()->toDateString();

        WorkSchedule::factory()
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
    }
}
