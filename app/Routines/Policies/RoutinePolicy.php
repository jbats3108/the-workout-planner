<?php

namespace App\Routines\Policies;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoutinePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin() && $ability !== 'update') {
            return true;
        }

        return null;
    }

    public function view(User $user, Routine $routine): bool
    {
        return $routine->user->is($user);
    }

    public function delete(User $user, Routine $routine): bool
    {
        return $routine->user->is($user);
    }

    public function update(User $user, Routine $routine): bool
    {
        return $routine->user->is($user);
    }

    public function duplicate(User $user, Routine $routine): bool
    {
        return $routine->user->is($user);
    }
}
