<?php

namespace App\Http\Controllers\RoutineTypes;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CreateRoutineTypeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('RoutineTypes/Create');
    }
}
