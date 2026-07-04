<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Dashboard\DTOs\DashboardPeriodItemData;
use App\Domain\Dashboard\DTOs\HoursSummaryData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HoursSummaryData */
class HoursSummaryJson extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'generated_at' => $this->generatedAt->toIso8601String(),
            'timezone' => $this->timezone,
            'balance' => [
                'balance_minutes' => $this->balance->balanceMinutes,
                'positive_minutes' => $this->balance->positiveMinutes,
                'negative_minutes' => $this->balance->negativeMinutes,
            ],
            'today' => [
                'date' => $this->today->date->toDateString(),
                'worked_minutes' => $this->today->workedMinutes,
                'paused_minutes' => $this->today->pausedMinutes,
                'expected_minutes' => $this->today->expectedMinutes,
                'regular_minutes' => $this->today->regularMinutes,
                'extra_minutes' => $this->today->extraMinutes,
                'missing_minutes' => $this->today->missingMinutes,
            ],
            'semester' => [
                'starts_at' => $this->semesterStartsAt->toDateString(),
                'ends_at' => $this->semesterEndsAt->toDateString(),
                'items' => $this->transformItems($this->semesterItems),
            ],
            'month' => [
                'starts_at' => $this->monthStartsAt->toDateString(),
                'ends_at' => $this->monthEndsAt->toDateString(),
                'balance_minutes' => $this->monthBalanceMinutes,
                'items' => $this->transformItems($this->monthItems),
            ],
        ];
    }

    /**
     * @param  iterable<int, DashboardPeriodItemData>  $items
     * @return array<int, array<string, mixed>>
     */
    private function transformItems(iterable $items): array
    {
        $transformed = [];

        foreach ($items as $item) {
            $transformed[] = [
                'date' => $item->date->toDateString(),
                'has_schedule' => $item->hasSchedule,
                'worked_minutes' => $item->workedMinutes,
                'expected_minutes' => $item->expectedMinutes,
                'regular_minutes' => $item->regularMinutes,
                'extra_minutes' => $item->extraMinutes,
                'missing_minutes' => $item->missingMinutes,
            ];
        }

        return $transformed;
    }
}
