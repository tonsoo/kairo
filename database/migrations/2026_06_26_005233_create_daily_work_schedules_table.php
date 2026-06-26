<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('work_schedule_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('weekday')->comment('ISO-8601 weekday: 1 = Monday, 7 = Sunday');
            $table->enum('type', ['total_time', 'time_range']);
            $table->unsignedSmallInteger('expected_minutes');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['user_id', 'date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_work_schedules');
    }
};
