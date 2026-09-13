<?php

namespace App\Http\Requests\Court;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sport_id' => ['required', 'exists:sports,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'pricing_rules' => ['nullable', 'array'],
            'pricing_rules.*.name' => ['nullable', 'string', 'max:100'],
            'pricing_rules.*.start_time' => ['required', 'date_format:H:i'],
            'pricing_rules.*.end_time' => ['required', 'date_format:H:i', 'after:pricing_rules.*.start_time'],
            'pricing_rules.*.price_per_hour' => ['required', 'numeric', 'min:10000'],
            'pricing_rules.*.day_type' => ['nullable', 'string', 'in:weekday,weekend,all'],
        ];
    }
}
