<?php

declare(strict_types=1);

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\DTOs\ShiftExportFormatData;
use App\Repositories\ShiftExportRegistry;

final readonly class ListShiftExportFormats
{
    public function __construct(
        private ShiftExportRegistry $shiftExportRegistry,
    ) {}

    /**
     * @return list<ShiftExportFormatData>
     */
    public function __invoke(): array
    {
        return array_map(
            fn ($repository) => new ShiftExportFormatData(
                key: $repository->type()->value,
                labelKey: $repository->labelKey(),
            ),
            $this->shiftExportRegistry->all(),
        );
    }
}
