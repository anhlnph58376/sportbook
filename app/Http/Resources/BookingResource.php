<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Booking
 */
class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'booking_date' => $this->booking_date->format('Y-m-d'),
            'start_time' => substr($this->start_time, 0, 5),
            'end_time' => substr($this->end_time, 0, 5),
            'duration_hours' => (float) $this->duration_hours,
            'total_price' => (float) $this->total_price,
            'deposit_amount' => (float) $this->deposit_amount,
            'deposit_percentage' => $this->deposit_percentage,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'notes' => $this->notes,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'refund_amount' => $this->refund_amount ? (float) $this->refund_amount : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'court' => [
                'id' => $this->court->id,
                'name' => $this->court->name,
                'sport' => $this->court->sport->name ?? null,
                'venue' => [
                    'id' => $this->court->venue->id,
                    'name' => $this->court->venue->name,
                    'address' => $this->court->venue->address,
                    'phone' => $this->court->venue->phone,
                ],
            ],
            'player' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
            ],
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'review' => new ReviewResource($this->whenLoaded('review')),
        ];
    }
}
