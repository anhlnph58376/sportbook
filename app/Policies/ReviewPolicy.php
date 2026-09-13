<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id && $booking->canBeReviewed();
    }

    public function reply(User $user, Review $review): bool
    {
        return $user->isAdmin() || $user->id === $review->venue->owner_id;
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
