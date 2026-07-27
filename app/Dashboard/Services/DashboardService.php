<?php

namespace App\Dashboard\Services;

use App\Dashboard\Data\DashboardData;
use App\Users\Models\User;

class DashboardService
{
    public function getDashboardData(User $user): DashboardData
    {
        return DashboardData::fromUser($user);
    }
}
