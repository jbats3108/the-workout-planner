<?php

namespace App\Workouts\Policies;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\Workout;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkoutPolicy
{
    use HandlesAuthorization;

    public function create(User $user, Routine $routine): bool
    {
        return $routine->user->is($user);
    }

    public function view(User $user, Workout $workout): bool
    {
        return $workout->user->is($user);
    }

    public function update(User $user, Workout $workout): bool
    {
        return $workout->user->is($user) && $workout->status === WorkoutStatus::InProgress;
    }

    public function applyProgression(User $user, Workout $workout): bool
    {
        return $workout->user->is($user) && $workout->status === WorkoutStatus::Finished;
    }

    public function editHistory(User $user, Workout $workout): bool
    {
        return $workout->user->is($user) && $workout->status === WorkoutStatus::Finished;
    }

    public function deleteHistory(User $user, Workout $workout): bool
    {
        return $workout->user->is($user) && $workout->status === WorkoutStatus::Finished;
    }
}
