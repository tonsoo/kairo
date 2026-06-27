<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class RemoveShiftBreakRequest extends FormRequest
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
            'previous_shift_id' => ['required', 'integer', 'exists:shifts,id', 'different:next_shift_id'],
            'next_shift_id' => ['required', 'integer', 'exists:shifts,id', 'different:previous_shift_id'],
        ];
    }
}
