<?php

namespace App\Http\Requests\Venue;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            'sport_ids' => ['required', 'array', 'min:1'],
            'sport_ids.*' => ['exists:sports,id'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['exists:amenities,id'],
            'operating_hours' => ['nullable', 'array'],
            'operating_hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'operating_hours.*.is_closed' => ['required', 'boolean'],
            'operating_hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'operating_hours.*.close_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
