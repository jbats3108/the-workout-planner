<?php

namespace App\MuscleGroups\Http\Controllers;

use App\MuscleGroups\Data\UpdateMuscleGroupData;
use App\Shared\Http\Controllers\Controller;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Http\RedirectResponse;

class UpdateMuscleGroupController extends Controller
{
    public function __invoke(UpdateMuscleGroupData $request, MuscleGroup $muscleGroup): RedirectResponse
    {
        $muscleGroup->update($request->toArray());

        return redirect(route('dashboard'));
    }
}
