<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\WorkSchedule\Actions\BuildReplaceWorkSchedulesData;
use App\Domain\WorkSchedule\Actions\SyncWorkSchedulesForEffectiveFrom;
use App\Domain\WorkSchedule\Exceptions\InvalidWorkScheduleConfiguration;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListWorkSchedulesRequest;
use App\Http\Requests\Api\ReplaceWorkSchedulesRequest;
use App\Http\Resources\WorkScheduleJson;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Support\Parsing\DateParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WorkScheduleController extends Controller
{
    public function index(ListWorkSchedulesRequest $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{from?: string|null} $validated */
        $validated = $request->validated();

        $rawFrom = $validated['from'] ?? null;
        $from = $rawFrom == null
            ? null
            : DateParser::parseLocalDate($rawFrom, $user->timezone, 'from');

        $query = WorkSchedule::query()
            ->where('user_id', $user->id)
            ->when(
                $from !== null,
                fn ($query) => $query->whereDate('effective_from', $from),
            )
            ->orderByDesc('effective_from')
            ->orderBy('weekday');

        return WorkScheduleJson::collection($query->get());
    }

    public function replace(
        ReplaceWorkSchedulesRequest $request,
        BuildReplaceWorkSchedulesData $buildReplaceWorkSchedulesData,
        SyncWorkSchedulesForEffectiveFrom $syncWorkSchedulesForEffectiveFrom,
    ): AnonymousResourceCollection|JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /**
         * @var array{
         *      effective_from: string,
         *      schedules: array<int, array{
         *          weekday: int,
         *          type: string,
         *          expected_minutes?: int|null,
         *          starts_at?: string|null,
         *          ends_at?: string|null
         *      }>
         * } $validated
         */
        $validated = $request->validated();

        $replaceWorkSchedulesData = ($buildReplaceWorkSchedulesData)($user, $validated);

        try {
            return WorkScheduleJson::collection(
                ($syncWorkSchedulesForEffectiveFrom)($user, $replaceWorkSchedulesData),
            );
        } catch (InvalidWorkScheduleConfiguration $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
