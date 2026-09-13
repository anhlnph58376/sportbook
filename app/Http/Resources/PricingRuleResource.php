<?php

namespace App\Http\Resources;

use App\Models\PricingRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PricingRule
 */
class PricingRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_time' => substr($this->start_time, 0, 5),
            'end_time' => substr($this->end_time, 0, 5),
            'price_per_hour' => (float) $this->price_per_hour,
            'day_type' => $this->day_type->value,
            'is_active' => $this->is_active,
        ];
    }
}
