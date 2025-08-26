<?php

namespace App\Http\Controllers\Routines;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteRoutineController extends Controller
{
    public function __invoke(Request $request, Routine $routine): RedirectResponse
    {
        $routine->delete();

        return back();
    }
}
