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
        $routine = new Routine($request->toArray());
        $routine->owner()->associate($request->owner);
        $routine->routineType()->associate($request->routineType);
        $routine->save();

        return redirect(route('dashboard'))->with('success', 'Routine has been created.');
    }
}
