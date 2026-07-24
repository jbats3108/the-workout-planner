<?php

namespace App\Routines\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Routines\Models\Routine;
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
