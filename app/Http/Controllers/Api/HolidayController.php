<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListHolidaysRequest;
use App\Http\Requests\Api\UpdateUserHolidayFollowRequest;
use App\Http\Resources\HolidayJson;
use App\Models\Holiday;
use App\Models\User;
use App\Support\Parsing\DateParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class HolidayController extends Controller
{
    public function index(ListHolidaysRequest $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{month: string, country_code?: string|null} $validated */
        $validated = $request->validated();

        $month = DateParser::parseLocalDate($validated['month'], 'UTC', 'month')->startOfMonth();
        $countryCode = $validated['country_code'] ?? null;

        $holidays = Holiday::query()
            ->whereBetween('date', [$month->toDateString(), $month->endOfMonth()->toDateString()])
            ->when(
                is_string($countryCode) && $countryCode !== '',
                fn ($query) => $query->where('country_code', strtoupper($countryCode)),
            )
            ->withExists([
                'users as is_followed' => fn ($query) => $query->where('users.id', $user->id),
            ])
            ->orderBy('date')
            ->orderBy('name')
            ->get();

        return HolidayJson::collection($holidays);
    }

    public function updateFollow(
        UpdateUserHolidayFollowRequest $request,
        Holiday $holiday,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $followed = (bool) $request->boolean('followed');

        if ($followed) {
            $user->holidays()->syncWithoutDetaching([$holiday->id]);
        } else {
            $user->holidays()->detach($holiday->id);
        }

        $holiday->setAttribute('is_followed', $followed);

        return response()->json([
            'data' => HolidayJson::make($holiday)->resolve(),
        ]);
    }
}
