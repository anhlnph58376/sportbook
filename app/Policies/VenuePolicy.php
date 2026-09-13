<?php

namespace App\Policies;

use App\Enums\VenueStatus;
use App\Enums\VenueVerificationStatus;
use App\Models\User;
use App\Models\Venue;

class VenuePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Venue $venue): bool
    {
        if ($venue->status === VenueStatus::Active && $venue->verification_status === VenueVerificationStatus::Approved) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->id === $venue->owner_id;
    }

    public function create(User $user): bool
    {
        return $user->isVenueOwner() || $user->isAdmin();
    }

    public function update(User $user, Venue $venue): bool
    {
        return $user->isAdmin() || $user->id === $venue->owner_id;
    }

    public function delete(User $user, Venue $venue): bool
    {
        return $user->isAdmin() || $user->id === $venue->owner_id;
    }

    public function approve(User $user): bool
    {
        return $user->isAdmin();
    }
}
