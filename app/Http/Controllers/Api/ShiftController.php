<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Shift\Actions\CreateShift;
use App\Domain\Shift\Actions\DeleteShift;
use App\Domain\Shift\Actions\UpdateShift;
use App\Domain\Shift\DTOs\ShiftPeriodData;
use App\Domain\Shift\Exceptions\InvalidShiftPeriod;
use App\Domain\Shift\Exceptions\ShiftOverlapDetected;
use App\Domain\Shift\Exceptions\ShiftOwnershipDenied;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListShiftsRequest;
use App\Http\Requests\Api\StoreShiftRequest;
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

        $timezone = $validated['timezone'] ?? $user->timezone;

        $query = Shift::query()
            ->whereBelongsTo($user)
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

    public function store(
        StoreShiftRequest $request,
        CreateShift $createShift,
    ): ShiftJson|JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{started_at: string, ended_at?: string|null, timezone?: string|null} $validated */
        $validated = $request->validated();

        try {
            return new ShiftJson(
                ($createShift)($user, $this->buildShiftPeriod($validated)),
            );
        } catch (InvalidShiftPeriod|ShiftOverlapDetected $exception) {
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

    private function parseLocalDate(?string $value, string $timezone, string $field): ?CarbonImmutable
    {
        return $value === null
            ? null
            : DateParser::parseLocalDate($value, $timezone, $field);
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
}
