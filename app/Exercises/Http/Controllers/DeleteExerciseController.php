<?php

namespace App\Exercises\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Exercises\Models\Exercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteExerciseController extends Controller
{
    public function __invoke(Request $request, Exercise $exercise): RedirectResponse
    {
        $exercise->delete();

        return back();
    }
}
