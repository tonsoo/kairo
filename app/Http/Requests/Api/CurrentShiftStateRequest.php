<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use DateTimeInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CurrentShiftStateRequest extends FormRequest
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
            'at' => ['nullable', Rule::date()->format(DateTimeInterface::ATOM)],
        ];
    }
}
