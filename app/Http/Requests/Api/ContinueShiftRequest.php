<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use DateTimeInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ContinueShiftRequest extends FormRequest
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
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }
}
