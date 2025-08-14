<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowDashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $service): Response
    {
        $data = $service->getDashboardData($request->user());

        return Inertia::render('Dashboard', [
            'data' => $data,
        ]);
    }
}
