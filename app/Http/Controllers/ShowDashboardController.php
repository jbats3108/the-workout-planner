<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowDashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboardService): Response
    {
        /** @var User $user */
        $user = $request->user();
        $dashboardData = $dashboardService->getDashboardData($user);

        return Inertia::render('Dashboard', [
            'data' => $dashboardData,
        ]);
    }
}
