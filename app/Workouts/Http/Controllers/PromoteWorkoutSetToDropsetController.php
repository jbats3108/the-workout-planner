<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
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
        $set->loadMissing('setGroup.block');
        abort_unless($set->setGroup->block->workout_id === $workout->id, 404);

        try {
            $workoutService->promoteToDropset($set, $data->segmentWeightGrams());
        } catch (WorkoutServiceException $exception) {
            return back()->withErrors(['set' => $exception->getMessage()]);
        }

        return back();
    }
}
