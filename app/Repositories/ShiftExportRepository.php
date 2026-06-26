<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Shift\DTOs\ShiftExportData;
use App\Domain\Shift\Enums\ShiftExportType;

interface ShiftExportRepository
{
    public function type(): ShiftExportType;

    public function labelKey(): string;

    public function extension(): string;

    public function mimeType(): string;

    public function export(ShiftExportData $data, string $locale): string;
}
