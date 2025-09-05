<?php

namespace App\Http\Controllers\Workouts;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use Illuminate\Http\Request;

class StoreWorkoutController extends Controller
{
    public function __invoke(Request $request, Routine $routine) {}
}
