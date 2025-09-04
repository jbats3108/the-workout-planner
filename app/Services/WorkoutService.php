<?php

namespace App\Services;

use App\Exceptions\WorkoutServiceException;
use App\Models\Routine;
use App\Models\Workout;

class WorkoutService
{
    public const ROUTINE_HAS_NO_EXERCISES_ERROR = 'Unable to create a workout for a routine with no exercises';

    /**
     * @throws WorkoutServiceException
     */
    public function createWorkout(Routine $routine): Workout
    {

        if ($routine->exercises->count() === 0) {
            throw new WorkoutServiceException(self::ROUTINE_HAS_NO_EXERCISES_ERROR);
        }

        return Workout::create([
            'routine_id' => $routine->id,
        ]);

    }
}
