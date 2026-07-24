<?php

namespace App\Http\Controllers\Routines;

use App\DataTransferObjects\Routines\StoreRoutineData;
use App\Http\Controllers\Controller;
use App\Models\Routine;
use Illuminate\Http\RedirectResponse;

class StoreRoutineController extends Controller
{
    public function __invoke(StoreRoutineData $request): RedirectResponse
    {
        Routine::create([
            'user_id' => $request->user->id,
            'name' => $request->name,
            'deload_weight_factor' => $request->deloadWeightFactor ?? 0.5,
            'deload_reps_factor' => $request->deloadRepsFactor ?? 2,
        ]);

        return redirect(route('dashboard'))->with('success', 'Routine has been created.');
    }
}
