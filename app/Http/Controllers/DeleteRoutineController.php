<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use Illuminate\Http\Request;

class DeleteRoutineController extends Controller
{
    public function __invoke(Request $request, Routine $routine)
    {
        $routine->delete();

        return redirect()->back();
    }
}
