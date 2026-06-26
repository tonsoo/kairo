<?php

declare(strict_types=1);

namespace App\Http\Controllers\Http;

use App\Domain\Shift\Actions\BuildShiftExportData;
use App\Domain\Shift\Enums\ShiftExportType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Http\ExportShiftsRequest;
use App\Models\User;
use App\Repositories\ShiftExportRegistry;
use App\Support\Parsing\DateParser;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ShiftExportController extends Controller
{
    public function __invoke(
        ExportShiftsRequest $request,
        BuildShiftExportData $buildShiftExportData,
        ShiftExportRegistry $shiftExportRegistry,
    ): StreamedResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{from: string, to: string, type: string, timezone?: string|null} $validated */
        $validated = $request->validated();
        $timezone = $validated['timezone'] ?? $user->timezone;

        $startsAt = DateParser::parseLocalDate($validated['from'], $timezone, 'from');
        $endsAt = DateParser::parseLocalDate($validated['to'], $timezone, 'to');

        $referenceMoment = DateParser::nowInTimezone($timezone);
        $type = ShiftExportType::from($validated['type']);
        $repository = $shiftExportRegistry->get($type);

        $exportData = ($buildShiftExportData)(
            $user,
            $startsAt,
            $endsAt,
            $referenceMoment,
            $timezone,
        );

        $content = $repository->export($exportData, app()->getLocale());
        $filename = sprintf(
            'shifts-%s_%s.%s',
            $startsAt->format('Y-m-d'),
            $endsAt->format('Y-m-d'),
            $repository->extension(),
        );

        return response()->streamDownload(
            static function () use ($content): void {
                echo $content;
            },
            $filename,
            [
                'Content-Type' => $repository->mimeType(),
            ],
        );
    }
}
