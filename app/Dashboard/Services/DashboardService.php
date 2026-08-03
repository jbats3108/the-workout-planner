<?php

namespace App\Dashboard\Services;

use App\Dashboard\Data\DashboardData;
use App\Users\Models\User;
use App\Workouts\Services\NormalsSinceDeloadCounter;

class DashboardService
{
    public function __construct(
        private readonly NormalsSinceDeloadCounter $normalsSinceDeloadCounter,
    ) {}

    public function getDashboardData(User $user): DashboardData
    {
        return DashboardData::fromUser($user, $this->normalsSinceDeloadCounter);
    }
}
