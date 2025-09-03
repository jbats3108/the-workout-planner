<?php

namespace App\Services;

use App\Exceptions\WorkoutServiceException;
use App\Models\Routine;

class WorkoutService
{
    public const ROUTINE_HAS_NO_EXERCISES_ERROR = 'Unable to create a workout for a routine with no exercises';

    /**
     * @throws WorkoutServiceException
     */
    public function createWorkout(Routine $routine): void
    {

        if ($routine->exercises->count() === 0) {
            throw new WorkoutServiceException(self::ROUTINE_HAS_NO_EXERCISES_ERROR);
        }

    }
}
