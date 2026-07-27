<?php

namespace App\Routines\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CreateRoutineController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('routines/Create');
    }
}
