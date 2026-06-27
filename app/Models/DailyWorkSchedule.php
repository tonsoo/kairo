<?php

namespace App\Models;

use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use Database\Factories\DailyWorkScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $work_schedule_id
 * @property Carbon $date
 * @property int $weekday
 * @property WorkScheduleType $type
 * @property int $expected_minutes
 * @property string|null $starts_at
 * @property string|null $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'work_schedule_id', 'date', 'weekday', 'type', 'expected_minutes', 'starts_at', 'ends_at'])]
class DailyWorkSchedule extends Model
{
    /** @use HasFactory<DailyWorkScheduleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weekday' => 'integer',
            'type' => WorkScheduleType::class,
            'expected_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<WorkSchedule, $this>
     */
    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }
}
