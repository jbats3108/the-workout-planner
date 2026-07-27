<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Models\Workout;
use App\Workouts\Services\WorkoutProgressionService;
use Illuminate\Http\RedirectResponse;

class SkipProgressionController extends Controller
{
    public function __invoke(Workout $workout, WorkoutProgressionService $progressionService): RedirectResponse
    {
        $progressionService->forgetProgressionSession($workout);

        return redirect()->route('dashboard');
    }
}
