<?php

declare(strict_types=1);

namespace App\Http\Requests\Panel;

use App\Domain\Shift\Enums\ShiftExportType;
use App\Models\User;
use App\Support\Parsing\DateParser;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class ExportShiftsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ShiftExportType::class)],
            'from' => ['required', Rule::date()->format(DateParser::localDateFormat)],
            'to' => ['required', Rule::date()->format(DateParser::localDateFormat)],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var User|null $user */
                $user = $this->user();

                if ($user === null) {
                    return;
                }

                /** @var array{from?: string|null, to?: string|null, timezone?: string|null} $validated */
                $validated = $validator->safe()->all();

                if (($validated['from'] ?? null) === null || ($validated['to'] ?? null) === null) {
                    return;
                }

                $timezone = $validated['timezone'] ?? $user->timezone;
                $startsAt = DateParser::parseLocalDate($validated['from'], $timezone, 'from');
                $endsAt = DateParser::parseLocalDate($validated['to'], $timezone, 'to');
                $today = DateParser::nowInTimezone($timezone)->startOfDay();

                $this->assertDateOrder($validator, $startsAt, $endsAt);
                $this->assertDateNotInFuture($validator, $startsAt, $endsAt, $today);
                $this->assertDateRangeLength($validator, $startsAt, $endsAt);
            },
        ];
    }

    private function assertDateOrder(
        Validator $validator,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
    ): void {
        if ($endsAt->lt($startsAt)) {
            $validator->errors()->add('to', 'The end date must be the same as or after the start date.');
        }
    }

    private function assertDateNotInFuture(
        Validator $validator,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        CarbonImmutable $today,
    ): void {
        if ($startsAt->gt($today)) {
            $validator->errors()->add('from', 'The start date cannot be in the future.');
        }

        if ($endsAt->gt($today)) {
            $validator->errors()->add('to', 'The end date cannot be in the future.');
        }
    }

    private function assertDateRangeLength(
        Validator $validator,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
    ): void {
        if ($endsAt->gt($startsAt->addMonths(6))) {
            $validator->errors()->add('to', 'The export date range cannot exceed six months.');
        }
    }
}
