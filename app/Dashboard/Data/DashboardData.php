<?php

namespace App\Dashboard\Data;

use App\Routines\Data\RoutineData;
use App\Users\Models\User;
use App\Workouts\Data\History\HistoryWorkoutItemData;
use App\Workouts\Data\InProgressWorkoutData;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Services\NormalsSinceDeloadCounter;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class DashboardData extends Data
{
    /**
     * @param  Collection<int, RoutineData>  $routines
     * @param  Collection<int, HistoryWorkoutItemData>  $recentFinishedWorkouts
     */
    public function __construct(
        public readonly Collection $routines,
        public readonly ?InProgressWorkoutData $inProgressWorkout,
        public readonly Collection $recentFinishedWorkouts,
    ) {}

    public static function fromUser(User $user, NormalsSinceDeloadCounter $normalsSinceDeloadCounter): DashboardData
    {
        $user->loadMissing(['routines.blocks.blockExercises']);

        $inProgress = $user->workouts()
            ->with('routine')
            ->where('status', WorkoutStatus::InProgress)
            ->latest('started_at')
            ->first();

        $recentFinished = $user->workouts()
            ->with('routine')
            ->where('status', WorkoutStatus::Finished)
            ->orderByDesc('finished_at')
            ->limit(5)
            ->get()
            ->map(fn ($workout) => HistoryWorkoutItemData::fromWorkout($workout));

        $normalsSinceDeload = $normalsSinceDeloadCounter->summarizeByRoutineId($user, $user->routines->pluck('id'));

        return new self(
            routines: $user->routines->map(function ($routine) use ($normalsSinceDeload) {
                $summary = $normalsSinceDeload[$routine->id] ?? ['count' => 0, 'has_finished_deload' => false];

                return RoutineData::fromRoutine(
                    $routine,
                    $summary['count'],
                    $summary['has_finished_deload'],
                );
            }),
            inProgressWorkout: $inProgress === null ? null : new InProgressWorkoutData(
                id: $inProgress->ulid,
                routineName: $inProgress->routine->getName(),
                mode: $inProgress->mode->value,
            ),
            recentFinishedWorkouts: $recentFinished,
        );
    }
}
