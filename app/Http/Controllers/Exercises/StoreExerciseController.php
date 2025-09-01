<?php

namespace App\Http\Controllers\Exercises;

use App\DataTransferObjects\Exercises\StoreExerciseData;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class StoreExerciseController extends Controller
{
    public function __invoke(StoreExerciseData $request): HttpResponse
    {

        $exercise = new Exercise($request->toArray());
        $exercise->primaryMuscleGroup()->associate($request->primaryMuscleGroup);
        $exercise->secondaryMuscleGroup()->associate($request->secondaryMuscleGroup);

        $exercise->save();

        return response('Successfully created exercise', Response::HTTP_CREATED);
    }
}
