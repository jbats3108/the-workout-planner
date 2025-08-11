<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\CreateRoutineData;
use App\Models\Routine;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateRoutineController extends Controller
{
    public function __invoke(CreateRoutineData $request): HttpResponse
    {
        $routine = new Routine($request->toArray());
        $routine->owner()->associate($request->owner);
        $routine->routineType()->associate($request->routineType);
        $routine->save();

        return response('Successfully created routine', Response::HTTP_CREATED);
    }
}
