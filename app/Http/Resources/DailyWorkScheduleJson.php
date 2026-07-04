<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DailyWorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DailyWorkSchedule */
class DailyWorkScheduleJson extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'weekday' => $this->weekday,
            'type' => $this->type->value,
            'expected_minutes' => $this->expected_minutes,
            'starts_at' => $this->starts_at === null ? null : substr($this->starts_at, 0, 5),
            'ends_at' => $this->ends_at === null ? null : substr($this->ends_at, 0, 5),
        ];
    }
}
