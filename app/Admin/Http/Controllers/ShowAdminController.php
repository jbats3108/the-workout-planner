<?php

namespace App\Admin\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ShowAdminController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Index');
    }
}
