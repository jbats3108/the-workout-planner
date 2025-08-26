<?php

namespace App\Http\Controllers\MuscleGroups;

use App\DataTransferObjects\MuscleGroups\UpdateMuscleGroupData;
use App\Http\Controllers\Controller;
use App\Models\MuscleGroup;
use Illuminate\Http\RedirectResponse;

class UpdateMuscleGroupController extends Controller
{
    public function __invoke(UpdateMuscleGroupData $request, MuscleGroup $muscleGroup): RedirectResponse
    {
        $muscleGroup->update($request->toArray());

        return redirect(route('dashboard'));
    }
}
