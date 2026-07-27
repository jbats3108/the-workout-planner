<?php

namespace App\Exercises\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteExerciseController extends Controller
{
    public function __invoke(Request $request, Exercise $exercise): RedirectResponse
    {
        $exercise->delete();

        return redirect()
            ->route('admin.exercises')
            ->with('success', 'Exercise deleted.');
    }
}
