<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
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
        $set->loadMissing('setGroup.block');
        abort_unless($set->setGroup->block->workout_id === $workout->id, 404);

        try {
            $workoutService->removeWorkingSetRound($set);
        } catch (WorkoutServiceException $exception) {
            return back()->withErrors(['workout' => $exception->getMessage()]);
        }

        return back();
    }
}
