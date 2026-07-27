<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Data\Progression\BumpProposalData;
use App\Workouts\Data\Progression\ProgressionPageData;
use App\Workouts\Data\Progression\UndoBumpProposalData;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\Workout;
use App\Workouts\Services\WorkoutProgressionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ShowProgressionController extends Controller
{
    public function __invoke(Workout $workout, WorkoutProgressionService $progressionService): Response|RedirectResponse
    {
        if ($workout->status !== WorkoutStatus::Finished) {
            return redirect()->route('dashboard');
        }

        if (! $progressionService->hasProgressionSession($workout)) {
            return redirect()->route('dashboard');
        }

        $storedBumps = session("workout_progression.{$workout->id}");
        $storedUndos = session("workout_progression_undos.{$workout->id}");

        $bumps = BumpProposalData::collect(is_array($storedBumps) ? $storedBumps : [], DataCollection::class);
        $undos = UndoBumpProposalData::collect(is_array($storedUndos) ? $storedUndos : [], DataCollection::class);

        $workout->loadMissing('routine');

        return Inertia::render('workouts/Progression', [
            'progression' => new ProgressionPageData(
                workoutId: $workout->id,
                routineName: $workout->routine->getName(),
                bumps: $bumps,
                undos: $undos,
            ),
        ]);
    }
}
