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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday')->comment('ISO-8601 weekday: 1 = Monday, 7 = Sunday');
            $table->enum('type', ['total_time', 'time_range']);
            $table->unsignedSmallInteger('expected_minutes');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->date('effective_from');
            $table->timestamps();

            $table->unique(['user_id', 'weekday', 'effective_from']);
            $table->index(['user_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
