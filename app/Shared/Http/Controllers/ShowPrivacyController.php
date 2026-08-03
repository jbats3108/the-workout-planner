<?php

namespace App\Shared\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ShowPrivacyController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Privacy');
    }
}
