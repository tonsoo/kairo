<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Domain\WorkSchedule\Enums\WorkScheduleType;
use App\Support\Parsing\DateParser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpsertDailyWorkScheduleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'date' => $this->route('date'),
        ]);
    }

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
            'date' => ['required', Rule::date()->format(DateParser::localDateFormat)],
            'type' => ['required', Rule::enum(WorkScheduleType::class)],
            'expected_minutes' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', Rule::date()->format(DateParser::localTimeFormat)],
            'ends_at' => ['nullable', Rule::date()->format(DateParser::localTimeFormat)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var array<string, mixed> $data */
                $data = $validator->safe()->all();
                $type = $data['type'] ?? null;

                if ($type === WorkScheduleType::totalTime->value && ($data['expected_minutes'] ?? null) === null) {
                    $validator->errors()->add('expected_minutes', 'Total time schedules require expected_minutes.');
                }

                if ($type === WorkScheduleType::dayOff->value) {
                    if (($data['expected_minutes'] ?? null) !== null) {
                        $validator->errors()->add('expected_minutes', 'Day off schedules do not accept expected_minutes.');
                    }

                    if (($data['starts_at'] ?? null) !== null) {
                        $validator->errors()->add('starts_at', 'Day off schedules do not accept starts_at.');
                    }

                    if (($data['ends_at'] ?? null) !== null) {
                        $validator->errors()->add('ends_at', 'Day off schedules do not accept ends_at.');
                    }

                    return;
                }

                if ($type !== WorkScheduleType::timeRange->value) {
                    return;
                }

                if (($data['starts_at'] ?? null) === null) {
                    $validator->errors()->add('starts_at', 'Time range schedules require starts_at.');
                }

                if (($data['ends_at'] ?? null) === null) {
                    $validator->errors()->add('ends_at', 'Time range schedules require ends_at.');
                }
            },
        ];
    }
}
