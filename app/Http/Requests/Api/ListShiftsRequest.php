<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Support\Parsing\DateParser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListShiftsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', Rule::date()->format(DateParser::localDateFormat)],
            'to' => ['nullable', Rule::date()->format(DateParser::localDateFormat), 'after_or_equal:from'],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }
}
