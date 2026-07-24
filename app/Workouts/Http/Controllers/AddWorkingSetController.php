<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutBlock;
use App\Workouts\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;

class AddWorkingSetController extends Controller
{
    public function __invoke(
        Workout $workout,
        WorkoutBlock $block,
        WorkoutService $workoutService,
    ): RedirectResponse {
        abort_unless($block->workout_id === $workout->id, 404);

        try {
            $workoutService->addWorkingSet($block);
        } catch (WorkoutServiceException $exception) {
            return back()->withErrors(['workout' => $exception->getMessage()]);
        }

        return back();
    }
}
