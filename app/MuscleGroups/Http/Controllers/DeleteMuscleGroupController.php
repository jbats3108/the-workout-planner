<?php

namespace App\MuscleGroups\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\MuscleGroups\Models\MuscleGroup;
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
