<?php

namespace App\Http\Controllers\Routines;

use App\DataTransferObjects\Routines\RoutineTypeData;
use App\Http\Controllers\Controller;
use App\Models\RoutineType;
use Inertia\Inertia;
use Inertia\Response;

class CreateRoutineController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Routines/Create', [
            'routine_types' => RoutineTypeData::collect(RoutineType::all()),
        ]);
    }
}
