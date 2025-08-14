<?php

namespace App\DataTransferObjects;

use App\DataTransferObjects\Routines\RoutineData;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class DashboardData extends Data
{
    public function __construct(
        /** @var Collection<int, RoutineData> */
        public readonly Collection $routines
    ) {}

    public static function fromUser(User $user): static
    {
        return new static(
            RoutineData::collect($user->routines)
        );
    }
}
