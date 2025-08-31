<?php

namespace App\Http\Controllers\RoutineTypes;

use App\Http\Controllers\Controller;
use App\Models\RoutineType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteRoutineTypeController extends Controller
{
    public function __invoke(Request $request, RoutineType $routineType): RedirectResponse
    {
        $routineType->delete();

        return back();
    }
}
