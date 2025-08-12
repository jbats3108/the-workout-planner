<?php

namespace App\Http\Controllers\Exercises;

use App\DataTransferObjects\Exercises\ExerciseData;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Request;

class ShowExerciseController extends Controller
{
    public function __invoke(Request $request, Exercise $exercise)
    {
        return ExerciseData::from($exercise);
    }
}
