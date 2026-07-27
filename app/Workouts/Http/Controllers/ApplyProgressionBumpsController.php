<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Data\Progression\ApplyBumpsData;
use App\Workouts\Data\Progression\BumpProposalData;
use App\Workouts\Models\Workout;
use App\Workouts\Services\WorkoutProgressionService;
use Illuminate\Http\RedirectResponse;
use Spatie\LaravelData\DataCollection;

class ApplyProgressionBumpsController extends Controller
{
    public function __invoke(
        Workout $workout,
        ApplyBumpsData $data,
        WorkoutProgressionService $progressionService,
    ): RedirectResponse {
        $stored = session()->pull("workout_progression.{$workout->id}");

        if (! is_array($stored) || $stored === []) {
            return redirect()->route('dashboard');
        }

        $proposals = BumpProposalData::collect($stored, DataCollection::class);
        $allowedIds = [];
        foreach ($proposals as $bump) {
            /** @var BumpProposalData $bump */
            $allowedIds[] = $bump->routineBlockExerciseId;
        }
        $selected = array_values(array_intersect($data->routineBlockExerciseIds, $allowedIds));

        $progressionService->applyConfirmedBumps($proposals, $selected);

        return redirect()->route('dashboard');
    }
}
