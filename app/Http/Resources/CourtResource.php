<?php

namespace App\Http\Resources;

use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Court
 */
class CourtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'venue_id' => $this->venue_id,
            'name' => $this->name,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'status' => $this->status->value,
            'sport' => new SportResource($this->whenLoaded('sport')),
            'pricing_rules' => PricingRuleResource::collection($this->whenLoaded('pricingRules')),
            'images' => $this->images->pluck('path'),
        ];
    }
}
