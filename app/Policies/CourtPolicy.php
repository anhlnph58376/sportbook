<?php

namespace App\Policies;

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;

class CourtPolicy
{
    public function create(User $user, Venue $venue): bool
    {
        return $user->isAdmin() || $user->id === $venue->owner_id;
    }

    public function update(User $user, Court $court): bool
    {
        return $user->isAdmin() || $user->id === $court->venue->owner_id;
    }

    public function delete(User $user, Court $court): bool
    {
        return $user->isAdmin() || $user->id === $court->venue->owner_id;
    }
}
