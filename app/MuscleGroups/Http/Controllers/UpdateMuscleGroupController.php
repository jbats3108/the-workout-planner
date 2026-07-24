<?php

namespace App\MuscleGroups\Http\Controllers;

use App\MuscleGroups\Data\UpdateMuscleGroupData;
use App\MuscleGroups\Models\MuscleGroup;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class UpdateMuscleGroupController extends Controller
{
    public function __invoke(UpdateMuscleGroupData $request, MuscleGroup $muscleGroup): RedirectResponse
    {
        $muscleGroup->update($request->toArray());

        return redirect()
            ->route('admin.muscle-groups')
            ->with('success', 'Muscle group updated.');
    }
}
