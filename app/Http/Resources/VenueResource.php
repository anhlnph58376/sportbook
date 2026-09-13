<?php

namespace App\Http\Resources;

use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Venue
 */
class VenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'province' => $this->province,
            'district' => $this->district,
            'ward' => $this->ward,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance_km' => isset($this->distance) ? round((float) $this->distance, 2) : null,
            'phone' => $this->phone,
            'opening_time' => substr($this->opening_time, 0, 5),
            'closing_time' => substr($this->closing_time, 0, 5),
            'status' => $this->status->value,
            'average_rating' => (float) $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'primary_image' => $this->primaryImage?->path,
            'sports' => SportResource::collection($this->whenLoaded('sports')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'courts_count' => $this->courts_count ?? $this->courts->count(),
        ];
    }
}
