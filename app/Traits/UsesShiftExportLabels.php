<?php

namespace App\Traits;

use App\Domain\Shift\DTOs\ShiftExportData;

trait UsesShiftExportLabels
{
    /**
     * @return array{weekday: string, date: string, duration: string}
     */
    protected static function exportHeadings(): array
    {
        return [
            'weekday' => __('exports.shift.headings.weekday'),
            'date' => __('exports.shift.headings.date'),
            'duration' => __('exports.shift.headings.duration'),
        ];
    }

    protected static function exportTitle(): string
    {
        return __('exports.shift.title');
    }

    protected static function exportPeriodHeading(): string
    {
        return __('exports.shift.period').':';
    }

    protected static function exportTimezoneHeading(): string
    {
        return __('exports.shift.timezone').':';
    }

    protected static function exportPeriodLabel(ShiftExportData $data): string
    {
        return sprintf(
            '%s %s - %s',
            self::exportPeriodHeading(),
            $data->startsAt->format('d/m/Y'),
            $data->endsAt->format('d/m/Y'),
        );
    }

    protected static function exportTimezoneLabel(ShiftExportData $data): string
    {
        return sprintf('%s %s', self::exportTimezoneHeading(), $data->timezone);
    }

    protected static function exportSheetName(): string
    {
        return __('exports.shift.sheet_name');
    }

    protected static function exportFooterLabel(): string
    {
        return __('exports.shift.footer');
    }
}
