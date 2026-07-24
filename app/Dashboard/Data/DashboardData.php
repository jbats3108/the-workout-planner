<?php

namespace App\Dashboard\Data;

use App\Routines\Data\RoutineData;
use App\Users\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

final class DashboardData extends Data
{
    public function __construct(
        /** @var Collection<int, RoutineData> */
        public readonly Collection $routines
    ) {}

    public static function fromUser(User $user): DashboardData
    {
        return new self(
            $user->routines->map(fn ($routine) => RoutineData::fromRoutine($routine))
        );
    }
}
