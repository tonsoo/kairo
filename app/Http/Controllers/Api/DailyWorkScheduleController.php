<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\WorkSchedule\Actions\UpsertDailyWorkSchedule;
use App\Domain\WorkSchedule\DTOs\WorkScheduleData;
use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Domain\WorkSchedule\Exceptions\InvalidWorkScheduleConfiguration;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShowDailyWorkScheduleRequest;
use App\Http\Requests\Api\UpsertDailyWorkScheduleRequest;
use App\Http\Resources\DailyWorkScheduleJson;
use App\Models\DailyWorkSchedule;
use App\Models\User;
use App\Support\Parsing\DateParser;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

final class DailyWorkScheduleController extends Controller
{
    public function show(ShowDailyWorkScheduleRequest $request, string $date): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $parsedDate = DateParser::parseLocalDate($date, $user->timezone, 'date');
        $dailyWorkSchedule = DailyWorkSchedule::query()
            ->whereBelongsTo($user)
            ->whereDate('date', $parsedDate)
            ->first();

        return response()->json([
            'data' => $dailyWorkSchedule === null
                ? null
                : DailyWorkScheduleJson::make($dailyWorkSchedule)->resolve(),
        ]);
    }

    public function upsert(
        UpsertDailyWorkScheduleRequest $request,
        string $date,
        UpsertDailyWorkSchedule $upsertDailyWorkSchedule,
    ): DailyWorkScheduleJson|JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{type: string, expected_minutes?: int|null, starts_at?: string|null, ends_at?: string|null} $validated */
        $validated = $request->validated();

        $parsedDate = DateParser::parseLocalDate($date, $user->timezone, 'date');

        try {
            return new DailyWorkScheduleJson(
                ($upsertDailyWorkSchedule)(
                    $user,
                    $parsedDate,
                    $this->buildWorkScheduleData($user, $parsedDate, $validated),
                ),
            );
        } catch (InvalidWorkScheduleConfiguration $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * @param  array{type: string, expected_minutes?: int|null, starts_at?: string|null, ends_at?: string|null}  $validated
     */
    private function buildWorkScheduleData(User $user, CarbonImmutable $date, array $validated): WorkScheduleData
    {
        $weekday = $date->isoWeekday();
        $type = WorkScheduleType::from($validated['type']);

        return match ($type) {
            WorkScheduleType::dayOff => WorkScheduleData::dayOff($weekday, $date),
            WorkScheduleType::totalTime => WorkScheduleData::totalTime(
                $weekday,
                $validated['expected_minutes'] ?? 0,
                $date,
            ),
            WorkScheduleType::timeRange => WorkScheduleData::timeRange(
                $weekday,
                DateParser::parseLocalTimeOnDate(
                    $validated['starts_at'] ?? '',
                    $date,
                    $user->timezone,
                    'starts_at',
                ),
                DateParser::parseLocalTimeOnDate(
                    $validated['ends_at'] ?? '',
                    $date,
                    $user->timezone,
                    'ends_at',
                ),
                $date,
            ),
        };
    }
}
