<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Shift\Actions\ContinueShift;
use App\Domain\Shift\Actions\DeleteShift;
use App\Domain\Shift\Actions\EndShift;
use App\Domain\Shift\Actions\RemoveShiftBreak;
use App\Domain\Shift\Actions\StartShift;
use App\Domain\Shift\Actions\UpdateShift;
use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Domain\Shift\Exceptions\InvalidShiftBreakRemoval;
use App\Domain\Shift\Exceptions\InvalidShiftPeriod;
use App\Domain\Shift\Exceptions\NoOngoingShiftFound;
use App\Domain\Shift\Exceptions\OngoingShiftAlreadyExists;
use App\Domain\Shift\Exceptions\ShiftOverlapDetected;
use App\Domain\Shift\Exceptions\ShiftOwnershipDenied;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContinueShiftRequest;
use App\Http\Requests\Api\EndShiftRequest;
use App\Http\Requests\Api\ListShiftsRequest;
use App\Http\Requests\Api\RemoveShiftBreakRequest;
use App\Http\Requests\Api\StartShiftRequest;
use App\Http\Requests\Api\UpdateShiftRequest;
use App\Http\Resources\ShiftJson;
use App\Models\Shift;
use App\Models\User;
use App\Support\Parsing\DateParser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ShiftController extends Controller
{
    public function index(ListShiftsRequest $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{from?: string|null, to?: string|null, timezone?: string|null} $validated */
        $validated = $request->validated();
        $timezone = $this->resolveTimezone($validated['timezone'] ?? null, $user);

        $query = Shift::query()
            ->where('user_id', $user->id)
            ->orderByDesc('started_at');

        $from = $this->parseLocalDate($validated['from'] ?? null, $timezone, 'from');

        if ($from !== null) {
            $query->where(function (Builder $query) use ($from): void {
                $query
                    ->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $from->utc());
            });
        }

        $to = $this->parseLocalDate($validated['to'] ?? null, $timezone, 'to');

        if ($to !== null) {
            $query->where('started_at', '<', $to->addDay()->utc());
        }

        return ShiftJson::collection($query->get());
    }

    public function start(
        StartShiftRequest $request,
        StartShift $startShift,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{at?: string|null, timezone?: string|null} $validated */
        $validated = $request->validated();

        try {
            return new ShiftJson(
                ($startShift)($user, $this->resolveMoment($validated, $user)),
            )->response()->setStatusCode(201);
        } catch (OngoingShiftAlreadyExists|ShiftOverlapDetected $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function end(
        EndShiftRequest $request,
        EndShift $endShift,
    ): ShiftJson|JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{at?: string|null, timezone?: string|null} $validated */
        $validated = $request->validated();

        try {
            return new ShiftJson(
                ($endShift)($user, $this->resolveMoment($validated, $user)),
            );
        } catch (NoOngoingShiftFound|ShiftOverlapDetected $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function continueMethod(
        ContinueShiftRequest $request,
        ContinueShift $continueShift,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{at?: string|null, timezone?: string|null} $validated */
        $validated = $request->validated();

        try {
            return new ShiftJson(
                ($continueShift)($user, $this->resolveMoment($validated, $user)),
            )->response()->setStatusCode(201);
        } catch (OngoingShiftAlreadyExists|ShiftOverlapDetected $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        UpdateShiftRequest $request,
        Shift $shift,
        UpdateShift $updateShift,
    ): ShiftJson|JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{started_at: string, ended_at?: string|null, timezone?: string|null} $validated */
        $validated = $request->validated();

        try {
            return new ShiftJson(
                ($updateShift)($user, $shift, $this->buildShiftPeriod($validated)),
            );
        } catch (ShiftOwnershipDenied $exception) {
            return response()->json(['message' => $exception->getMessage()], 403);
        } catch (InvalidShiftPeriod|ShiftOverlapDetected $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function removeBreak(
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

    public function destroy(
        Shift $shift,
        DeleteShift $deleteShift,
        Request $request,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        try {
            ($deleteShift)($user, $shift);
        } catch (ShiftOwnershipDenied $exception) {
            return response()->json(['message' => $exception->getMessage()], 403);
        }

        return response()->json(['deleted' => true]);
    }

    /**
     * @param  array{at?: string|null, timezone?: string|null}  $validated
     */
    private function resolveMoment(array $validated, User $user): CarbonImmutable
    {
        $at = $validated['at'] ?? null;
        $timezone = $this->resolveTimezone($validated['timezone'] ?? null, $user);

        if ($at === null) {
            return DateParser::nowInTimezone($timezone);
        }

        return DateParser::parseAtomDateTime($at, 'at');
    }

    private function parseLocalDate(?string $value, string $timezone, string $field): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        return DateParser::parseLocalDate($value, $timezone, $field);
    }

    /**
     * @param  array{started_at: string, ended_at?: string|null, timezone?: string|null}  $validated
     */
    private function buildShiftPeriod(array $validated): ShiftPeriodData
    {
        return new ShiftPeriodData(
            startedAt: DateParser::parseAtomDateTime($validated['started_at'], 'started_at'),
            endedAt: ($validated['ended_at'] ?? null) === null
                ? null
                : DateParser::parseAtomDateTime($validated['ended_at'], 'ended_at'),
        );
    }

    private function resolveTimezone(?string $timezone, User $user): string
    {
        return $timezone ?? $user->timezone;
    }
}
