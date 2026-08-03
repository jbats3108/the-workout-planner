<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Shared\Http\DomainFail;
use App\Workouts\Data\CompleteWorkoutSetData;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Services\WorkoutHistoryService;
use Illuminate\Http\RedirectResponse;

class UpdateWorkoutHistorySetController extends Controller
{
    public function __invoke(
        CompleteWorkoutSetData $data,
        Workout $workout,
        WorkoutSet $set,
        WorkoutHistoryService $historyService,
    ): RedirectResponse {
        try {
            $session = $historyService->updateWorkingSet($workout, $set, $data);
        } catch (WorkoutServiceException $exception) {
            return DomainFail::back($exception, 'set');
        }

        if ($session !== null) {
            return redirect()->route('workouts.progression', $workout);
        }

        return back();
    }
}
