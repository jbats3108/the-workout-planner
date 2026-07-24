<?php

namespace App\Dashboard\Data;

use App\Routines\Data\RoutineData;
use App\Users\Models\User;
use App\Workouts\Data\InProgressWorkoutData;
use App\Workouts\Enums\WorkoutStatus;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class DashboardData extends Data
{
    /**
     * @param  Collection<int, RoutineData>  $routines
     */
    public function __construct(
        public readonly Collection $routines,
        public readonly ?InProgressWorkoutData $inProgressWorkout = null,
    ) {}

    public static function fromUser(User $user): DashboardData
    {
        $inProgress = $user->workouts()
            ->with('routine')
            ->where('status', WorkoutStatus::InProgress)
            ->latest('started_at')
            ->first();

        return new self(
            routines: $user->routines->map(fn ($routine) => RoutineData::fromRoutine($routine)),
            inProgressWorkout: $inProgress === null ? null : new InProgressWorkoutData(
                id: $inProgress->id,
                routineName: $inProgress->routine->getName(),
                mode: $inProgress->mode->value,
            ),
        );
    }
}
