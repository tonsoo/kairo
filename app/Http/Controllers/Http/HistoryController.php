<?php

declare(strict_types=1);

namespace App\Http\Controllers\Http;

use App\Domain\Shift\Actions\ListShiftExportFormats;
use App\Domain\Shift\DTOs\ShiftExportFormatData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class HistoryController extends Controller
{
    public function __invoke(ListShiftExportFormats $listShiftExportFormats): Response
    {
        return Inertia::render('History', [
            'shiftExportFormats' => array_map(
                fn (ShiftExportFormatData $format): array => [
                    'key' => $format->key,
                    'label_key' => $format->labelKey,
                ],
                ($listShiftExportFormats)(),
            ),
        ]);
    }
}
