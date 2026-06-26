<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Dashboard\Actions\GetHoursSummary;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\HoursSummaryRequest;
use App\Http\Resources\HoursSummaryJson;
use App\Models\User;
use App\Support\Parsing\DateParser;

final class HoursSummaryController extends Controller
{
    public function __invoke(
        HoursSummaryRequest $request,
        GetHoursSummary $getHoursSummary,
    ): HoursSummaryJson {
        /** @var User $user */
        $user = $request->user();

        /** @var array{at?: string|null, month?: string|null, semester_start?: string|null} $validated */
        $validated = $request->validated();
        $referenceMoment = ($validated['at'] ?? null) === null
            ? DateParser::nowInTimezone($user->timezone)
            : DateParser::parseAtomDateTime($validated['at'], 'at');
        $monthStart = ($validated['month'] ?? null) === null
            ? $referenceMoment->startOfMonth()
            : DateParser::parseLocalDate($validated['month'], $user->timezone, 'month');
        $semesterStart = ($validated['semester_start'] ?? null) === null
            ? $referenceMoment->startOfMonth()->subMonths(5)
            : DateParser::parseLocalDate($validated['semester_start'], $user->timezone, 'semester_start');

        return new HoursSummaryJson(
            ($getHoursSummary)(
                $user,
                $referenceMoment,
                $monthStart,
                $semesterStart,
            ),
        );
    }
}
