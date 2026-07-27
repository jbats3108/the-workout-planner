<?php

namespace App\Workouts\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Workouts\Models\Workout;
use Illuminate\Http\RedirectResponse;

class SkipProgressionController extends Controller
{
    public function __invoke(Workout $workout): RedirectResponse
    {
        session()->forget("workout_progression.{$workout->id}");

        return redirect()->route('dashboard');
    }
}
