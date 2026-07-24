<?php

namespace App\MuscleGroups\Policies;

use App\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MuscleGroupPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }
}
