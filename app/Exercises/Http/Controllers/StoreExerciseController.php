<?php

namespace App\Exercises\Http\Controllers;

use App\Exercises\Data\StoreExerciseData;
use App\Exercises\Models\Exercise;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StoreExerciseController extends Controller
{
    public function __invoke(StoreExerciseData $request): RedirectResponse
    {
        $exercise = new Exercise([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);
        $exercise->primaryMuscleGroup()->associate($request->primaryMuscleGroup);
        $exercise->secondaryMuscleGroup()->associate($request->secondaryMuscleGroup);
        $exercise->save();

        return redirect()
            ->route('admin.exercises')
            ->with('success', 'Exercise created.');
    }
}
