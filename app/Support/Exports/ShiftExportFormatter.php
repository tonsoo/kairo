<?php

declare(strict_types=1);

namespace App\Support\Exports;

use App\Domain\Shift\DTOs\ShiftExportData;
use App\Domain\Shift\DTOs\ShiftExportDayData;

final class ShiftExportFormatter
{
    /**
     * @return list<array{weekday: string, date: string, duration: string}>
     */
    public static function buildDayRows(ShiftExportData $data, string $locale): array
    {
        return $data->days
            ->filter(
                fn (ShiftExportDayData $day): bool => $day->workedMinutes > 0 || $day->expectedMinutes > 0,
            )
            ->map(
                fn (ShiftExportDayData $day): array => [
                    'weekday' => self::weekdayLabel($day->date->dayOfWeekIso, $locale),
                    'date' => $day->date->format('d/m'),
                    'duration' => self::formatDuration($day->workedMinutes),
                ],
            )
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, duration: string}>
     */
    public static function buildSummaryRows(ShiftExportData $data, string $locale): array
    {
        $rows = [
            [
                'label' => self::summaryLabel('total', $locale),
                'duration' => self::formatDuration($data->workedMinutes),
            ],
            [
                'label' => self::summaryLabel('regular', $locale),
                'duration' => self::formatDuration($data->regularMinutes),
            ],
            [
                'label' => self::summaryLabel('extra', $locale),
                'duration' => self::formatDuration($data->extraMinutes),
            ],
        ];

        if ($data->missingMinutes > 0) {
            $rows[] = [
                'label' => self::summaryLabel('missing', $locale),
                'duration' => self::formatDuration($data->missingMinutes),
            ];
        }

        return $rows;
    }

    /**
     * @return array{weekday: string, date: string, duration: string}
     */
    public static function headings(string $locale): array
    {
        return match ($locale) {
            'pt-BR' => [
                'weekday' => 'Dia',
                'date' => 'Data',
                'duration' => 'Horas',
            ],
            default => [
                'weekday' => 'Day',
                'date' => 'Date',
                'duration' => 'Hours',
            ],
        };
    }

    public static function title(string $locale): string
    {
        return match ($locale) {
            'pt-BR' => 'Exportacao de horas',
            default => 'Hours export',
        };
    }

    public static function periodLabel(ShiftExportData $data, string $locale): string
    {
        $label = match ($locale) {
            'pt-BR' => 'Periodo',
            default => 'Period',
        };

        return sprintf(
            '%s: %s - %s',
            $label,
            $data->startsAt->format('d/m/Y'),
            $data->endsAt->format('d/m/Y'),
        );
    }

    public static function timezoneLabel(ShiftExportData $data, string $locale): string
    {
        $label = match ($locale) {
            'pt-BR' => 'Fuso horario',
            default => 'Timezone',
        };

        return sprintf('%s: %s', $label, $data->timezone);
    }

    public static function sheetName(string $locale): string
    {
        return match ($locale) {
            'pt-BR' => 'Horas',
            default => 'Hours',
        };
    }

    private static function weekdayLabel(int $weekday, string $locale): string
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

    private static function summaryLabel(string $key, string $locale): string
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

    private static function formatDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        return sprintf('%d:%02dh', $hours, $remainder);
    }
}
