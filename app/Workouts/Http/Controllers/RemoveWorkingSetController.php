<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Shared\Http\DomainFail;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;

class RemoveWorkingSetController extends Controller
{
    public function __invoke(
        Workout $workout,
        WorkoutSet $set,
        WorkoutService $workoutService,
    ): RedirectResponse {
        $set->assertBelongsToWorkout($workout);

        try {
            $workoutService->removeWorkingSetRound($set);
        } catch (WorkoutServiceException $exception) {
            return DomainFail::back($exception, 'workout');
        }

        return back();
    }
}
