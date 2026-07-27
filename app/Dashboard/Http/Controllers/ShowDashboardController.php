<?php

namespace App\Dashboard\Http\Controllers;

use App\Dashboard\Services\DashboardService;
use App\Shared\Http\Controllers\Controller;
use App\Users\Models\User;
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
