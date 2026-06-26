<?php

declare(strict_types=1);

namespace App\Domain\Shift\Enums;

enum ShiftExportType: string
{
    case Csv = 'csv';
    case Xlsx = 'xlsx';
    case Pdf = 'pdf';
}
