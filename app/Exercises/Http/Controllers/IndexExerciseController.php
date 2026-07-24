<?php

namespace App\Exercises\Http\Controllers;

use App\Exercises\Data\ExerciseData;
use App\Shared\Http\Controllers\Controller;
use App\Exercises\Models\Exercise;
use Illuminate\Http\JsonResponse;

class IndexExerciseController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(ExerciseData::collect(Exercise::all()));
    }
}
