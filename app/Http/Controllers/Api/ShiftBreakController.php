<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Shift\Actions\RemoveShiftBreak;
use App\Domain\Shift\Exceptions\InvalidShiftBreakRemoval;
use App\Domain\Shift\Exceptions\ShiftOverlapDetected;
use App\Domain\Shift\Exceptions\ShiftOwnershipDenied;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RemoveShiftBreakRequest;
use App\Http\Resources\ShiftJson;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class ShiftBreakController extends Controller
{
    public function destroy(
        RemoveShiftBreakRequest $request,
        RemoveShiftBreak $removeShiftBreak,
    ): ShiftJson|JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{previous_shift_id: int, next_shift_id: int} $validated */
        $validated = $request->validated();

        $previousShift = Shift::query()->findOrFail($validated['previous_shift_id']);
        $nextShift = Shift::query()->findOrFail($validated['next_shift_id']);

        try {
            return new ShiftJson(
                ($removeShiftBreak)($user, $previousShift, $nextShift),
            );
        } catch (ShiftOwnershipDenied $exception) {
            return response()->json(['message' => $exception->getMessage()], 403);
        } catch (InvalidShiftBreakRemoval|ShiftOverlapDetected $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
