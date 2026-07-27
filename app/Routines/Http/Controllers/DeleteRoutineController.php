<?php

namespace App\Routines\Http\Controllers;

use App\Routines\Models\Routine;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteRoutineController extends Controller
{
    public function __invoke(Request $request, Routine $routine): RedirectResponse
    {
        $routine->delete();

        return redirect()->route('dashboard');
    }
}
