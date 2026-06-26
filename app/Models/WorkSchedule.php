<?php

namespace App\Models;

use Database\Factories\WorkScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $weekday
 * @property string $type
 * @property int $expected_minutes
 * @property string|null $starts_at
 * @property string|null $ends_at
 * @property Carbon $effective_from
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'weekday', 'type', 'expected_minutes', 'starts_at', 'ends_at', 'effective_from'])]
class WorkSchedule extends Model
{
    /** @use HasFactory<WorkScheduleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'expected_minutes' => 'integer',
            'effective_from' => 'date',
        ];
    }

    /**
     * Get the user that owns the work schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the daily work schedules that were built from this work schedule.
     */
    public function dailyWorkSchedules(): HasMany
    {
        return $this->hasMany(DailyWorkSchedule::class);
    }
}
