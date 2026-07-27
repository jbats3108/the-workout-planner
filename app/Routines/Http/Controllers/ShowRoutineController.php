<?php

namespace App\Routines\Http\Controllers;

use App\Routines\Models\Routine;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ShowRoutineController extends Controller
{
    public function __invoke(Routine $routine): RedirectResponse
    {
        return redirect()->route('routines.edit', $routine);
    }
}
