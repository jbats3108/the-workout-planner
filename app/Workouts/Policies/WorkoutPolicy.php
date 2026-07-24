<?php

namespace App\Workouts\Policies;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkoutPolicy
{
    use HandlesAuthorization;

    public function create(User $user, Routine $routine): bool
    {
        return $routine->user->is($user);
    }
}
