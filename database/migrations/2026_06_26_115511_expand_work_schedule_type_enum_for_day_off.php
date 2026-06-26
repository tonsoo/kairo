<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->string('type', 32)->change();
        });

        Schema::table('daily_work_schedules', function (Blueprint $table) {
            $table->string('type', 32)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('work_schedules')
            ->where('type', 'day_off')
            ->update([
                'type' => 'total_time',
                'expected_minutes' => 0,
            ]);

        DB::table('daily_work_schedules')
            ->where('type', 'day_off')
            ->update([
                'type' => 'total_time',
                'expected_minutes' => 0,
            ]);

        Schema::table('work_schedules', function (Blueprint $table) {
            $table->enum('type', ['total_time', 'time_range'])->change();
        });

        Schema::table('daily_work_schedules', function (Blueprint $table) {
            $table->enum('type', ['total_time', 'time_range'])->change();
        });
    }
};
