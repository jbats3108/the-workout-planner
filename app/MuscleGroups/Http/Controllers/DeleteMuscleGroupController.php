<?php

namespace App\MuscleGroups\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteMuscleGroupController extends Controller
{
    public function __invoke(Request $request, MuscleGroup $muscleGroup): RedirectResponse
    {
        $inUse = Exercise::query()
            ->where(function ($query) use ($muscleGroup): void {
                $query->where('primary_muscle_group_id', $muscleGroup->id)
                    ->orWhere('secondary_muscle_group_id', $muscleGroup->id);
            })
            ->exists();

        if ($inUse) {
            return redirect()
                ->route('admin.muscle-groups')
                ->with('error', 'Cannot delete a muscle group that is still used by exercises.');
        }

        $muscleGroup->delete();

        return redirect()
            ->route('admin.muscle-groups')
            ->with('success', 'Muscle group deleted.');
    }
}
