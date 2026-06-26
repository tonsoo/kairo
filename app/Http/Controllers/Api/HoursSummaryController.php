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

        /** @var array{at?: string|null} $validated */
        $validated = $request->validated();
        $at = $validated['at'] ?? null;

        return new HoursSummaryJson(
            ($getHoursSummary)(
                $user,
                $at === null
                    ? DateParser::nowInTimezone($user->timezone)
                    : DateParser::parseAtomDateTime($at, 'at'),
            ),
        );
    }
}
