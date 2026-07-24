<?php

namespace App\Routines\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Routines\Models\Routine;
use Illuminate\Http\RedirectResponse;

class ShowRoutineController extends Controller
{
    public function __invoke(Routine $routine): RedirectResponse
    {
        return redirect()->route('routines.edit', $routine);
    }
}
