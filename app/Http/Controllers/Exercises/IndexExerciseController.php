<?php

namespace App\Http\Controllers\Exercises;

use App\DataTransferObjects\Exercises\ExerciseData;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;

class IndexExerciseController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(ExerciseData::collect(Exercise::all()));
    }
}
