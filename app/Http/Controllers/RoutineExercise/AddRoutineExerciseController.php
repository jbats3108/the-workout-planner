<?php

namespace App\Http\Controllers\RoutineExercise;

use App\DataTransferObjects\Routines\AddRoutineExerciseData;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Routine;
use Illuminate\Http\Response;

class AddRoutineExerciseController extends Controller
{
    public function __invoke(AddRoutineExerciseData $request, Routine $routine, Exercise $exercise): Response
    {
        $routine->exercises()->attach($exercise, $request->toArray());

        return response([
            'message' => 'Success',
        ]);
    }
}
