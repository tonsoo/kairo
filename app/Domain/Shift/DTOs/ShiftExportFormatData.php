<?php

declare(strict_types=1);

namespace App\Domain\Shift\DTOs;

final readonly class ShiftExportFormatData
{
    public function __construct(
        public string $key,
        public string $labelKey,
    ) {}
}
