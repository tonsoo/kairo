<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Shift\Actions\ResolveCurrentShiftState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CurrentShiftStateRequest;
use App\Http\Resources\CurrentShiftStateJson;
use App\Models\User;
use App\Support\Parsing\DateParser;

final class CurrentShiftStateController extends Controller
{
    public function __invoke(
        CurrentShiftStateRequest $request,
        ResolveCurrentShiftState $getCurrentShiftState,
    ): CurrentShiftStateJson {
        /** @var User $user */
        $user = $request->user();

        /** @var array{at?: string|null, timezone?: string|null} $validated */
        $validated = $request->validated();
        $at = $validated['at'] ?? null;
        $timezone = $validated['timezone'] ?? $user->timezone;

        return new CurrentShiftStateJson(
            ($getCurrentShiftState)(
                $user,
                $at === null
                    ? DateParser::nowInTimezone($timezone)
                    : DateParser::parseAtomDateTime($at, 'at'),
            ),
        );
    }
}
