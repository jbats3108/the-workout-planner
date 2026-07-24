<?php

namespace App\MuscleGroups\Http\Controllers;

use App\MuscleGroups\Data\StoreMuscleGroupData;
use App\MuscleGroups\Models\MuscleGroup;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StoreMuscleGroupController extends Controller
{
    public function __invoke(StoreMuscleGroupData $request): RedirectResponse
    {
        MuscleGroup::create($request->toArray());

        return redirect()
            ->route('admin.muscle-groups')
            ->with('success', 'Muscle group created.');
    }
}
