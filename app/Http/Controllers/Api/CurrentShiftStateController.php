<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Shift\Actions\GetCurrentShiftState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CurrentShiftStateRequest;
use App\Http\Resources\CurrentShiftStateJson;
use App\Models\User;
use App\Support\Parsing\DateParser;

final class CurrentShiftStateController extends Controller
{
    public function __invoke(
        CurrentShiftStateRequest $request,
        GetCurrentShiftState $getCurrentShiftState,
    ): CurrentShiftStateJson {
        /** @var User $user */
        $user = $request->user();

        /** @var array{at?: string|null} $validated */
        $validated = $request->validated();
        $at = $validated['at'] ?? null;

        return new CurrentShiftStateJson(
            ($getCurrentShiftState)(
                $user,
                $at === null
                    ? DateParser::nowInTimezone($user->timezone)
                    : DateParser::parseAtomDateTime($at, 'at'),
            ),
        );
    }
}
