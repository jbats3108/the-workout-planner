<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Models\Workout;
use App\Workouts\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;

class FinishWorkoutController extends Controller
{
    public function __invoke(Workout $workout, WorkoutService $workoutService): RedirectResponse
    {
        try {
            $bumps = $workoutService->finishWorkout($workout);
        } catch (WorkoutServiceException $exception) {
            return back()->withErrors(['workout' => $exception->getMessage()]);
        }

        if ($bumps->count() === 0) {
            return redirect()->route('dashboard');
        }

        session([
            "workout_progression.{$workout->id}" => $bumps->toArray(),
        ]);

        return redirect()->route('workouts.progression', $workout);
    }
}
