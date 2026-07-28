<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Models\Workout;
use Illuminate\Http\RedirectResponse;

class DeleteWorkoutHistoryController extends Controller
{
    public function __invoke(Workout $workout): RedirectResponse
    {
        $workout->delete();

        return redirect()
            ->route('history.index')
            ->with('success', 'Workout removed from history.');
    }
}
