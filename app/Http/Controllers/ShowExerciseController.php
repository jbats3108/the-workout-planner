<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Request;

class ShowExerciseController extends Controller
{
    public function __invoke(Request $request, Exercise $exercise)
    {
        return $exercise;
    }
}
