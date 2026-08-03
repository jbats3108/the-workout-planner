<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Shared\Http\DomainFail;
use App\Workouts\Data\PromoteWorkoutSetToDropsetData;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;

class PromoteWorkoutSetToDropsetController extends Controller
{
    public function __invoke(
        PromoteWorkoutSetToDropsetData $data,
        Workout $workout,
        WorkoutSet $set,
        WorkoutService $workoutService,
    ): RedirectResponse {
        $set->assertBelongsToWorkout($workout);

        try {
            $workoutService->promoteToDropset($set, $data->segmentWeightGrams());
        } catch (WorkoutServiceException $exception) {
            return DomainFail::back($exception, 'set');
        }

        return back();
    }
}
