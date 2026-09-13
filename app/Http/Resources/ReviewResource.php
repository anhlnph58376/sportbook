<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_visible' => $this->is_visible,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar,
            ],
            'images' => $this->images->pluck('path'),
            'reply' => $this->reply ? [
                'comment' => $this->reply->comment,
                'replied_by' => $this->reply->user->name ?? 'Chủ sân',
                'created_at' => $this->reply->created_at?->toIso8601String(),
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
