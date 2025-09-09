<?php

namespace App\Http\Controllers\Workouts;

use App\Exceptions\WorkoutServiceException;
use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class StoreWorkoutController extends Controller
{
    public function __invoke(Request $request, Routine $routine, WorkoutService $workoutService): HttpResponse|RedirectResponse
    {
        try {
            $workoutService->createWorkout($routine);
        } catch (WorkoutServiceException $exception) {
            return back()->withErrors($exception->getMessage());
        }

        return response('Successfully created workout', Response::HTTP_CREATED);
    }
}
