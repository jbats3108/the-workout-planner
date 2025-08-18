<?php

namespace App\Http\Controllers\Exercises;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteExerciseController extends Controller
{
    public function __invoke(Request $request, Exercise $exercise): RedirectResponse
    {
        $exercise->delete();

        return redirect()->back();
    }
}
