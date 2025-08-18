<?php

namespace App\Http\Controllers\Routines;

use App\DataTransferObjects\Routines\RoutineData;
use App\Http\Controllers\Controller;
use App\Models\Routine;
use Illuminate\Http\Request;

class ShowRoutineController extends Controller
{
    public function __invoke(Request $request, Routine $routine): RoutineData
    {
        return RoutineData::from($routine);
    }
}
