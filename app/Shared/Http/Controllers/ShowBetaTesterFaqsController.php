<?php

namespace App\Shared\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ShowBetaTesterFaqsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('BetaTesterFaqs', [
            'interestFormUrl' => null,
            'feedbackFormUrl' => null,
        ]);
    }
}
