<?php

namespace App\Exercises\Http\Controllers;

use App\Exercises\Data\ExerciseData;
use App\Shared\Http\Controllers\Controller;
use App\Exercises\Models\Exercise;
use Request;

class ShowExerciseController extends Controller
{
    public function __invoke(Request $request, Exercise $exercise): ExerciseData
    {
        return ExerciseData::from($exercise);
    }
}
