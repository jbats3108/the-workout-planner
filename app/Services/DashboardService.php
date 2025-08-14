<?php

namespace App\Services;

use App\DataTransferObjects\DashboardData;
use App\Models\User;

class DashboardService
{
    public function getDashboardData(User $user): DashboardData
    {
        return DashboardData::fromUser($user);
    }
}
