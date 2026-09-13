<?php

namespace App\Http\Requests\Court;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'price_per_hour' => ['required', 'numeric', 'min:10000'],
            'day_type' => ['nullable', 'string', 'in:weekday,weekend,all'],
        ];
    }
}
