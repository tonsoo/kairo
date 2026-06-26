<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create();

        Shift::factory()
            ->count(3)
            ->for($user)
            ->sequence(
                [
                    'started_at' => CarbonImmutable::today()->subDays(3)->setTime(9, 0),
                    'ended_at' => CarbonImmutable::today()->subDays(3)->setTime(17, 0),
                ],
                [
                    'started_at' => CarbonImmutable::today()->subDays(2)->setTime(9, 0),
                    'ended_at' => CarbonImmutable::today()->subDays(2)->setTime(17, 0),
                ],
                [
                    'started_at' => CarbonImmutable::today()->subDay()->setTime(9, 0),
                    'ended_at' => CarbonImmutable::today()->subDay()->setTime(17, 0),
                ],
            )
            ->create();

        Shift::factory()
            ->ongoing()
            ->for($user)
            ->create([
                'started_at' => CarbonImmutable::today()->setTime(9, 0),
            ]);
    }
}
