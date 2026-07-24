<?php

namespace App\Routines\Http\Controllers;

use App\Routines\Data\RoutineData;
use App\Shared\Http\Controllers\Controller;
use App\Routines\Models\Routine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexRoutineController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $routines = $user->hasPermissionTo('view all routines')
            ? Routine::all()
            : $user->routines;

        return response()->json(
            $routines->map(fn (Routine $routine) => RoutineData::fromRoutine($routine))
        );
    }
}
