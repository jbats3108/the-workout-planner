<?php

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoutinePolicy
{
    use HandlesAuthorization;

    public function delete(User $user, Routine $routine): bool
    {
        return $user->hasRole('admin') || $user->id === $routine->owner_id;
    }
}
