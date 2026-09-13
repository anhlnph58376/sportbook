<?php

namespace App\Http\Requests\Court;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive,under_maintenance'],
        ];
    }
}
