<?php

namespace App\Http\Controllers\MuscleGroups;

use App\DataTransferObjects\MuscleGroups\StoreMuscleGroupData;
use App\Http\Controllers\Controller;
use App\Models\MuscleGroup;
use Illuminate\Http\RedirectResponse;

class StoreMuscleGroupController extends Controller
{
    public function __invoke(StoreMuscleGroupData $request): RedirectResponse
    {
        MuscleGroup::create($request->toArray());

        return redirect(route('dashboard'));
    }
}
