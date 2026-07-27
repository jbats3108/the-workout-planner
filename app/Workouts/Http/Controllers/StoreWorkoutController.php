<?php

namespace App\Workouts\Http\Controllers;

use App\Routines\Models\Routine;
use App\Shared\Http\Controllers\Controller;
use App\Workouts\Data\StoreWorkoutData;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;

class StoreWorkoutController extends Controller
{
    public function __invoke(StoreWorkoutData $data, Routine $routine, WorkoutService $workoutService): RedirectResponse
    {
        try {
            $workout = $workoutService->createWorkout($routine, $data->modeOrDefault());
        } catch (WorkoutServiceException $exception) {
            return back()->withErrors(['workout' => $exception->getMessage()]);
        }

        return redirect()->route('workouts.play', $workout);
    }
}
