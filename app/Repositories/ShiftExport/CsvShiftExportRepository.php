<?php

declare(strict_types=1);

namespace App\Repositories\ShiftExport;

use App\Domain\Shift\DTOs\ShiftExportData;
use App\Domain\Shift\DTOs\ShiftExportDayData;
use App\Domain\Shift\Enums\ShiftExportType;
use App\Traits\UsesShiftExportSummaryRows;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;

final class CsvShiftExportRepository implements ShiftExportRepository
{
    use UsesShiftExportSummaryRows;

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

    /**
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function export(ShiftExportData $data): string
    {
        $writer = Writer::fromString();

        foreach ($data->days as $day) {
            /** @var ShiftExportDayData $day */
            $writer->insertOne([
                $day->weekdayLabel(),
                $day->dateAsDM(),
                $day->durationAsReadableHours(),
            ]);
        }

        $writer->insertOne([]);

        foreach (self::buildSummaryRows($data) as $label => $duration) {
            $writer->insertOne([
                '',
                $label,
                $duration,
            ]);
        }

        return $writer->toString();
    }
}
