<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Holiday */
class HolidayJson extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'country_code' => $this->country_code,
            'is_national' => $this->is_national,
            'name' => $this->name,
            'is_followed' => (bool) ($this->resource->getAttribute('is_followed') ?? false),
        ];
    }
}
