<?php

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;
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
        return $routine->owner->is($user);
    }

    public function delete(User $user, Routine $routine): bool
    {
        return $routine->owner->is($user);
    }

    public function update(User $user, Routine $routine): bool
    {
        return $routine->owner->is($user);
    }
}
