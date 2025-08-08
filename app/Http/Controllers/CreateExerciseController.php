<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExerciseRequest;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateExerciseController extends Controller
{
    public function __invoke(CreateExerciseRequest $request): HttpResponse
    {
        $validated = $request->validated();

        $primaryMuscleGroup = MuscleGroup::lookup($validated['primary_muscle_group']);
        $secondaryMuscleGroup = $validated['secondary_muscle_group'] ? MuscleGroup::lookup($validated['secondary_muscle_group']) : null;

        $createData = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'primary_muscle_group_id' => $primaryMuscleGroup->id,
            'secondary_muscle_group_id' => $secondaryMuscleGroup?->id,
            'movement_type' => $validated['movement_type'],
            'difficulty' => $validated['difficulty'],
            'equipment' => $validated['equipment'],
        ];

        Exercise::create($createData);

        return response('Successfully created exercise', Response::HTTP_CREATED);
    }
}
