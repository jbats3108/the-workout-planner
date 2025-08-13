<?php

namespace App\Http\Controllers\Routines;

use App\DataTransferObjects\Routines\AddRoutineExerciseData;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Routine;

class AddRoutineExerciseController extends Controller
{
    public function __invoke(AddRoutineExerciseData $request, Routine $routine, Exercise $exercise)
    {
        $routine->exercises()->attach($exercise, $request->toArray());

        return response([
            'message' => 'Success',
        ]);
    }
}
