<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class DeleteExerciseController extends Controller
{
    public function __invoke(Request $request, Exercise $exercise)
    {
        $exercise->delete();

        return redirect()->back();
    }
}
