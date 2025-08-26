<?php

namespace App\Http\Controllers\MuscleGroups;

use App\Http\Controllers\Controller;
use App\Models\MuscleGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteMuscleGroupController extends Controller
{
    public function __invoke(Request $request, MuscleGroup $muscleGroup): RedirectResponse
    {
        $muscleGroup->delete();

        return back();
    }
}
