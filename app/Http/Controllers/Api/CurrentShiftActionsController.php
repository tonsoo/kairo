<?php

namespace App\Http\Controllers\Api;

use App\Domain\Shift\Actions\ContinueShift;
use App\Domain\Shift\Actions\EndShift;
use App\Domain\Shift\Actions\StartShift;
use App\Domain\Shift\Exceptions\NoOngoingShiftFound;
use App\Domain\Shift\Exceptions\OngoingShiftAlreadyExists;
use App\Domain\Shift\Exceptions\ShiftOverlapDetected;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContinueShiftRequest;
use App\Http\Requests\Api\EndShiftRequest;
use App\Http\Requests\Api\StartShiftRequest;
use App\Http\Resources\ShiftJson;
use App\Models\User;
use App\Support\Parsing\DateParser;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class CurrentShiftActionsController extends Controller
{
    public function start(StartShiftRequest $request, StartShift $startShift): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            return (new ShiftJson(
                ($startShift)($user, $this->resolveMoment($request->validated(), $user)),
            ))->response()->setStatusCode(201);
        } catch (OngoingShiftAlreadyExists|ShiftOverlapDetected $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function end(EndShiftRequest $request, EndShift $endShift): ShiftJson|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            return new ShiftJson(
                ($endShift)($user, $this->resolveMoment($request->validated(), $user)),
            );
        } catch (NoOngoingShiftFound|ShiftOverlapDetected $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function resume(ContinueShiftRequest $request, ContinueShift $continueShift): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            return (new ShiftJson(
                ($continueShift)($user, $this->resolveMoment($request->validated(), $user)),
            ))->response()->setStatusCode(201);
        } catch (OngoingShiftAlreadyExists|ShiftOverlapDetected $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    /**
     * @param  array{at?: string|null, timezone?: string|null}  $validated
     */
    private function resolveMoment(array $validated, User $user): CarbonImmutable
    {
        $timezone = $validated['timezone'] ?? $user->timezone;

        return ($validated['at'] ?? null) === null
            ? DateParser::nowInTimezone($timezone)
            : DateParser::parseAtomDateTime($validated['at'], 'at');
    }
}
