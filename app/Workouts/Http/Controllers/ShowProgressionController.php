<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Data\Progression\BumpProposalData;
use App\Workouts\Data\Progression\ProgressionPageData;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\Workout;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ShowProgressionController extends Controller
{
    public function __invoke(Workout $workout): Response|RedirectResponse
    {
        if ($workout->status !== WorkoutStatus::Finished) {
            return redirect()->route('dashboard');
        }

        $stored = session("workout_progression.{$workout->id}");

        if (! is_array($stored) || $stored === []) {
            return redirect()->route('dashboard');
        }

        $bumps = BumpProposalData::collect($stored, DataCollection::class);

        $workout->loadMissing('routine');

        return Inertia::render('workouts/Progression', [
            'progression' => new ProgressionPageData(
                workoutId: $workout->id,
                routineName: $workout->routine->getName(),
                bumps: $bumps,
            ),
        ]);
    }
}
