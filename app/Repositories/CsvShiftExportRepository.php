<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Shift\DTOs\ShiftExportData;
use App\Domain\Shift\DTOs\ShiftExportDayData;
use App\Domain\Shift\Enums\ShiftExportType;
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

        foreach ($data->days as $day) {
            if (! $this->shouldIncludeDay($day)) {
                continue;
            }

            $writer->insertOne([
                $this->weekdayLabel($day->date->dayOfWeekIso, $locale),
                $day->date->format('d/m'),
                $this->formatDuration($day->workedMinutes),
            ]);
        }

        $writer->insertOne([]);
        $writer->insertAll([
            [$this->summaryLabel('regular', $locale), $this->formatDuration($data->regularMinutes)],
            [$this->summaryLabel('extra', $locale), $this->formatDuration($data->extraMinutes)],
            [$this->summaryLabel('total', $locale), $this->formatDuration($data->workedMinutes)],
        ]);

        if ($data->missingMinutes > 0) {
            $writer->insertOne([
                '',
                $this->summaryLabel('missing', $locale),
                $this->formatDuration($data->missingMinutes),
            ]);
        }

        return $writer->toString();
    }

    private function shouldIncludeDay(ShiftExportDayData $day): bool
    {
        return $day->workedMinutes > 0 || $day->expectedMinutes > 0;
    }

    private function weekdayLabel(int $weekday, string $locale): string
    {
        return match ($locale) {
            'pt-BR' => match ($weekday) {
                1 => 'SEG',
                2 => 'TER',
                3 => 'QUA',
                4 => 'QUI',
                5 => 'SEX',
                6 => 'SAB',
                7 => 'DOM',
            },
            default => match ($weekday) {
                1 => 'MON',
                2 => 'TUE',
                3 => 'WED',
                4 => 'THU',
                5 => 'FRI',
                6 => 'SAT',
                7 => 'SUN',
            },
        };
    }

    private function summaryLabel(string $key, string $locale): string
    {
        return match ($locale) {
            'pt-BR' => match ($key) {
                'total' => 'TOTAL',
                'regular' => 'Normais',
                'extra' => 'Extras',
                'missing' => 'Faltando',
            },
            default => match ($key) {
                'total' => 'TOTAL',
                'regular' => 'Regular',
                'extra' => 'Extra',
                'missing' => 'Missing',
            },
        };
    }

    private function formatDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        return sprintf('%d:%02dh', $hours, $remainder);
    }
}
