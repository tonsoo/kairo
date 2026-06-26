<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Shift\DTOs\CurrentShiftStateData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CurrentShiftStateData */
class CurrentShiftStateJson extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'next_action' => $this->nextAction->value,
            'local_date' => $this->localDate->toDateString(),
            'has_shift_today' => $this->hasShiftToday,
            'has_ongoing_shift' => $this->hasOngoingShift,
            'active_shift' => $this->activeShift === null ? null : new ShiftJson($this->activeShift),
            'latest_shift' => $this->latestShift === null ? null : new ShiftJson($this->latestShift),
        ];
    }
}
