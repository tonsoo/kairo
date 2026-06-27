<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Support\Parsing\DateParser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class ReplaceWorkSchedulesRequest extends FormRequest
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
            'effective_from' => ['required', Rule::date()->format(DateParser::localDateFormat)],
            'schedules' => ['required', 'array'],
            'schedules.*' => ['array:weekday,type,expected_minutes,starts_at,ends_at'],
            'schedules.*.weekday' => ['required', 'integer', 'between:1,7', 'distinct'],
            'schedules.*.type' => ['required', Rule::enum(WorkScheduleType::class)],
            'schedules.*.expected_minutes' => ['nullable', 'integer', 'min:0'],
            'schedules.*.starts_at' => ['nullable', Rule::date()->format(DateParser::localTimeFormat)],
            'schedules.*.ends_at' => ['nullable', Rule::date()->format(DateParser::localTimeFormat)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $schedules = $validator->safe()->all()['schedules'] ?? [];

                foreach ($schedules as $index => $schedule) {
                    if (! is_array($schedule)) {
                        continue;
                    }

                    $type = $schedule['type'] ?? null;

                    if ($type === WorkScheduleType::totalTime->value && ($schedule['expected_minutes'] ?? null) === null) {
                        $validator->errors()->add(
                            "schedules.$index.expected_minutes",
                            'Total time schedules require expected_minutes.',
                        );
                    }

                    if ($type === WorkScheduleType::dayOff->value) {
                        if (($schedule['expected_minutes'] ?? null) !== null) {
                            $validator->errors()->add(
                                "schedules.$index.expected_minutes",
                                'Day off schedules do not accept expected_minutes.',
                            );
                        }

                        if (($schedule['starts_at'] ?? null) !== null) {
                            $validator->errors()->add(
                                "schedules.$index.starts_at",
                                'Day off schedules do not accept starts_at.',
                            );
                        }

                        if (($schedule['ends_at'] ?? null) !== null) {
                            $validator->errors()->add(
                                "schedules.$index.ends_at",
                                'Day off schedules do not accept ends_at.',
                            );
                        }

                        continue;
                    }

                    if ($type !== WorkScheduleType::timeRange->value) {
                        continue;
                    }

                    if (($schedule['starts_at'] ?? null) === null) {
                        $validator->errors()->add(
                            "schedules.$index.starts_at",
                            'Time range schedules require starts_at.',
                        );
                    }

                    if (($schedule['ends_at'] ?? null) === null) {
                        $validator->errors()->add(
                            "schedules.$index.ends_at",
                            'Time range schedules require ends_at.',
                        );
                    }
                }
            },
        ];
    }
}
