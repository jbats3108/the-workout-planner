<?php

namespace App\Http\Controllers\Routines;

use App\DataTransferObjects\Routines\RoutineData;
use App\Http\Controllers\Controller;
use App\Models\Routine;
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

        return response()->json(RoutineData::collect($routines));
    }
}
