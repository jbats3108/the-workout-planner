<?php

namespace App\Routines\Http\Controllers;

use App\Routines\Data\StoreRoutineData;
use App\Routines\Models\Routine;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StoreRoutineController extends Controller
{
    public function __invoke(StoreRoutineData $request): RedirectResponse
    {
        $routine = Routine::create([
            'user_id' => $request->user->id,
            'name' => $request->name,
            'deload_weight_factor' => $request->deloadWeightFactor ?? 0.5,
            'deload_reps_factor' => $request->deloadRepsFactor ?? 2,
        ]);

        return redirect(route('routines.edit', $routine))->with('success', 'Routine has been created.');
    }
}
