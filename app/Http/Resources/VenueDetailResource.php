<?php

namespace App\Http\Resources;

use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Venue
 */
class VenueDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'province' => $this->province,
            'district' => $this->district,
            'ward' => $this->ward,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'email' => $this->email,
            'opening_time' => substr($this->opening_time, 0, 5),
            'closing_time' => substr($this->closing_time, 0, 5),
            'status' => $this->status->value,
            'verification_status' => $this->verification_status->value,
            'average_rating' => (float) $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'images' => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'path' => $img->path,
                'is_primary' => $img->is_primary,
            ]),
            'sports' => SportResource::collection($this->sports),
            'amenities' => AmenityResource::collection($this->amenities),
            'operating_hours' => $this->operatingHours->map(fn ($h) => [
                'day_of_week' => $h->day_of_week,
                'is_closed' => $h->is_closed,
                'open_time' => $h->open_time ? substr($h->open_time, 0, 5) : null,
                'close_time' => $h->close_time ? substr($h->close_time, 0, 5) : null,
            ]),
            'courts' => CourtResource::collection($this->courts),
            'is_favorite' => auth('sanctum')->check()
                ? $this->favoritedBy()->where('user_id', auth('sanctum')->id())->exists()
                : false,
        ];
    }
}
