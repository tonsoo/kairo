<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Support\Parsing\DateParser;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class HoursSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'at' => ['nullable', Rule::date()->format(DateTimeInterface::ATOM)],
            'month' => ['nullable', Rule::date()->format(DateParser::localDateFormat)],
            'semester_start' => ['nullable', Rule::date()->format(DateParser::localDateFormat)],
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

                /** @var array{at?: string|null, month?: string|null, semester_start?: string|null, timezone?: string|null} $validated */
                $validated = $validator->safe()->all();
                $timezone = $validated['timezone'] ?? $user->timezone;
                $referenceMoment = ($validated['at'] ?? null) === null
                    ? DateParser::nowInTimezone($timezone)
                    : DateParser::parseAtomDateTime($validated['at'], 'at');
                $currentMonthStart = $referenceMoment->startOfMonth();
                $currentSemesterStart = $currentMonthStart->subMonths(5);

                $this->assertPeriodStart(
                    $validator,
                    $validated['month'] ?? null,
                    'month',
                    $timezone,
                    $currentMonthStart,
                    'Month must be the first day of a month and cannot be in the future.',
                );
                $this->assertPeriodStart(
                    $validator,
                    $validated['semester_start'] ?? null,
                    'semester_start',
                    $timezone,
                    $currentSemesterStart,
                    'Semester start must be the first day of a month and cannot be in the future.',
                );
            },
        ];
    }

    private function assertPeriodStart(
        Validator $validator,
        ?string $value,
        string $field,
        string $timezone,
        CarbonImmutable $latestAllowedStart,
        string $message,
    ): void {
        if ($value === null) {
            return;
        }

        $date = DateParser::parseLocalDate($value, $timezone, $field);

        if (! $date->equalTo($date->startOfMonth()) || $date->gt($latestAllowedStart)) {
            $validator->errors()->add($field, $message);
        }
    }
}
