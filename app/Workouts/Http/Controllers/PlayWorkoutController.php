<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Users\Models\User;
use App\Workouts\Data\Player\WorkoutPlayerPageData;
use App\Workouts\Models\Workout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayWorkoutController extends Controller
{
    public function __invoke(Request $request, Workout $workout): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('workouts/Play', [
            'workout' => WorkoutPlayerPageData::fromWorkout($workout, $user),
        ]);
    }
}
