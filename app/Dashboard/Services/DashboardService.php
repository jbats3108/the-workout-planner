<?php

namespace App\Dashboard\Services;

use App\Dashboard\Data\DashboardData;
use App\Users\Models\User;
use App\Workouts\Services\StandardsSinceDeloadCounter;

class DashboardService
{
    public function __construct(
        private readonly StandardsSinceDeloadCounter $standardsSinceDeloadCounter,
    ) {}

    public function getDashboardData(User $user): DashboardData
    {
        return DashboardData::fromUser($user, $this->standardsSinceDeloadCounter);
    }
}
