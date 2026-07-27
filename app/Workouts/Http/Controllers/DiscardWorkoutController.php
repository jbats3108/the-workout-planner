<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Models\Workout;
use App\Workouts\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;

class DiscardWorkoutController extends Controller
{
    public function __invoke(Workout $workout, WorkoutService $workoutService): RedirectResponse
    {
        try {
            $workoutService->discardWorkout($workout);
        } catch (WorkoutServiceException $exception) {
            return back()->withErrors(['workout' => $exception->getMessage()]);
        }

        return redirect()->route('dashboard')->with('success', 'Workout abandoned.');
    }
}
