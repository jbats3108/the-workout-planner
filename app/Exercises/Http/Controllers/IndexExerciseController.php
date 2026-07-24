<?php

namespace App\Exercises\Http\Controllers;

use App\Exercises\Data\ExerciseData;
use App\Exercises\Models\Exercise;
use App\Shared\Http\Controllers\Controller;
use App\Users\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexExerciseController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $exercises = Exercise::query()
            ->forUser($user)
            ->orderBy('name')
            ->get();

        return response()->json(ExerciseData::collect($exercises));
    }
}
