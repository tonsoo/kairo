<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Shift;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Shift */
class ShiftJson extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $timezone = $request->user()?->timezone ?? 'UTC';
        $startedAt = CarbonImmutable::instance($this->started_at)->setTimezone($timezone);
        $endedAt = $this->ended_at === null
            ? null
            : CarbonImmutable::instance($this->ended_at)->setTimezone($timezone);

        return [
            'id' => $this->id,
            'timezone' => $timezone,
            'started_at' => $startedAt->toIso8601String(),
            'ended_at' => $endedAt?->toIso8601String(),
            'duration_minutes' => $endedAt?->diffInMinutes($startedAt),
        ];
    }
}
