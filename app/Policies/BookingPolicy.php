<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin()
            || $user->id === $booking->user_id
            || $user->id === $booking->court->venue->owner_id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->id === $booking->user_id;
    }

    public function checkIn(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->id === $booking->court->venue->owner_id;
    }

    public function reject(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->id === $booking->court->venue->owner_id;
    }
}
