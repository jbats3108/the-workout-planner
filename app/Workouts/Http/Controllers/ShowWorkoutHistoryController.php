<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Data\History\HistoryDetailPageData;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\Workout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowWorkoutHistoryController extends Controller
{
    public function __invoke(Request $request, Workout $workout): Response|RedirectResponse
    {
        if ($workout->status !== WorkoutStatus::Finished) {
            return redirect()
                ->route('history.index')
                ->with('error', 'That workout is not in history yet.');
        }

        return Inertia::render('history/Show', [
            'history' => HistoryDetailPageData::fromWorkout($workout, $request->user()),
        ]);
    }
}
