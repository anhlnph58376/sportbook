<?php

namespace App\Http\Requests\Venue;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVenueRequest extends FormRequest
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
            'address' => ['sometimes', 'required', 'string', 'max:500'],
            'province' => ['sometimes', 'required', 'string', 'max:100'],
            'district' => ['sometimes', 'required', 'string', 'max:100'],
            'ward' => ['sometimes', 'nullable', 'string', 'max:100'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'opening_time' => ['sometimes', 'required', 'date_format:H:i'],
            'closing_time' => ['sometimes', 'required', 'date_format:H:i'],
            'sport_ids' => ['sometimes', 'array'],
            'sport_ids.*' => ['exists:sports,id'],
            'amenity_ids' => ['sometimes', 'array'],
            'amenity_ids.*' => ['exists:amenities,id'],
        ];
    }
}
