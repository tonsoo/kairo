<?php

namespace App\Traits;

use App\Domain\Shift\DTOs\ShiftExportData;
use App\Support\Formatting\TimeFormatter;

trait UsesShiftExportSummaryRows
{
    /**
     * @return array<string, string>
     */
    public static function buildSummaryRows(ShiftExportData $data): array
    {
        $rows = [
            __('exports.shift.summary.total') => TimeFormatter::formatMinutesToReadableHours($data->workedMinutes),
            __('exports.shift.summary.regular') => TimeFormatter::formatMinutesToReadableHours($data->regularMinutes),
            __('exports.shift.summary.extra') => TimeFormatter::formatMinutesToReadableHours($data->extraMinutes),
        ];

        if ($data->missingMinutes > 0) {
            $rows[__('exports.shift.summary.missing')] = TimeFormatter::formatMinutesToReadableHours($data->missingMinutes);
        }

        return $rows;
    }
}
