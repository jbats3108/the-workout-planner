<?php

namespace App\Shared\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ShowFeedbackController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Feedback');
    }
}
