<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Exercises\ExerciseData;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;

class IndexExerciseController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(ExerciseData::collect(Exercise::all()));
    }
}
