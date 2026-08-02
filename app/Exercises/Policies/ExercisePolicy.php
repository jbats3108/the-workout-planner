<?php

namespace App\Exercises\Policies;

use App\Exercises\Models\Exercise;
use App\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExercisePolicy
{
    use HandlesAuthorization;

    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->isAdmin() && $exercise->isShared();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
}
