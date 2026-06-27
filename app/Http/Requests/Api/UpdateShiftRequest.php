<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use DateTimeInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateShiftRequest extends FormRequest
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
            'started_at' => ['required', Rule::date()->format(DateTimeInterface::ATOM)],
            'ended_at' => ['nullable', Rule::date()->format(DateTimeInterface::ATOM), 'after:started_at'],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }
}
