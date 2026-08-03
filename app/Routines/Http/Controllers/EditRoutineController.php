<?php

namespace App\Routines\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\Routines\Data\Editor\RoutineEditorExerciseOptionData;
use App\Routines\Data\Editor\RoutineEditorPageData;
use App\Routines\Models\Routine;
use App\Shared\Http\Controllers\Controller;
use App\Users\Enums\WarmUpDefaultsScope;
use App\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class EditRoutineController extends Controller
{
    public function __invoke(Request $request, Routine $routine): Response
    {
        /** @var User $user */
        $user = $request->user();

        $page = RoutineEditorPageData::fromRoutine(
            $routine,
            RoutineEditorExerciseOptionData::collect([], DataCollection::class),
            $user->weight_unit->value,
        );

        $payload = $page->toArray();

        return Inertia::render('routines/Edit', [
            'routine' => Arr::except($payload, ['exercises', 'weight_unit']),
            'exercises' => Inertia::defer(fn () => Exercise::query()
                ->with(['primaryMuscleGroup', 'secondaryMuscleGroup'])
                ->forUser($user)
                ->orderBy('name')
                ->get()
                ->map(fn (Exercise $exercise) => new RoutineEditorExerciseOptionData(
                    id: $exercise->id,
                    name: $exercise->getName(),
                    primaryMuscleGroup: $exercise->primaryMuscleGroup->getName(),
                ))
                ->values()
                ->all()),
            'weight_unit' => $payload['weight_unit'],
            'warm_up_defaults' => $user->resolvedWarmUpStepsDefault(),
            'warm_up_defaults_scope' => ($user->warm_up_defaults_scope ?? WarmUpDefaultsScope::AllBlocks)->value,
            'achievement_floor_default' => $user->achievement_floor_default,
        ]);
    }
}
