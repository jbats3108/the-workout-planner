<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexRoutineController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($request->user()->hasPermissionTo('view all routines')) {
            return response()->json(Routine::all());
        }

        return response()->json(Routine::whereBelongsTo($request->user(), 'owner')->get());
    }
}
