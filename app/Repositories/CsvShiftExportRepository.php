<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Shift\DTOs\ShiftExportData;
use App\Domain\Shift\Enums\ShiftExportType;
use App\Support\Exports\ShiftExportFormatter;
use League\Csv\Writer;

final class CsvShiftExportRepository implements ShiftExportRepository
{
    public function type(): ShiftExportType
    {
        return ShiftExportType::Csv;
    }

    public function labelKey(): string
    {
        return 'exports.type.csv';
    }

    public function extension(): string
    {
        return 'csv';
    }

    public function mimeType(): string
    {
        return 'text/csv; charset=UTF-8';
    }

    public function export(ShiftExportData $data, string $locale): string
    {
        $writer = Writer::createFromString();

        foreach (ShiftExportFormatter::buildDayRows($data, $locale) as $day) {
            $writer->insertOne([
                $day['weekday'],
                $day['date'],
                $day['duration'],
            ]);
        }

        $writer->insertOne([]);

        foreach (ShiftExportFormatter::buildSummaryRows($data, $locale) as $summary) {
            $writer->insertOne([
                '',
                $summary['label'],
                $summary['duration'],
            ]);
        }

        return $writer->toString();
    }
}
