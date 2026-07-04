<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Support\Parsing\DateParser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ShowDailyWorkScheduleRequest extends FormRequest
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
        ];
    }
}
