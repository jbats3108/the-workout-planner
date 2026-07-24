<?php

namespace App\Workouts\Data\Player;

use App\Shared\Enums\SetGroupType;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Models\WorkoutWarmUpStep;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class WorkoutPlayerSetData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $workoutBlockExerciseId,
        public readonly string $exerciseName,
        public readonly int $setIndex,
        public readonly string $groupType,
        public readonly ?float $targetWeightKg,
        public readonly ?int $targetReps,
        public readonly ?float $loggedWeightKg,
        public readonly ?int $loggedReps,
        public readonly bool $completed,
        public readonly int $restSeconds,
    ) {}

    public static function fromSet(
        WorkoutSet $set,
        string $exerciseName,
        int $workingWeightG,
        int $prescribedReps,
        SetGroupType $groupType,
        int $restSeconds,
        ?WorkoutWarmUpStep $warmUpStep = null,
    ): self {
        $targetWeightG = $workingWeightG;
        if ($groupType === SetGroupType::WarmUp && $warmUpStep !== null) {
            $targetWeightG = (int) round($workingWeightG * ($warmUpStep->percent_of_working / 100));
        }

        return new self(
            id: $set->id,
            workoutBlockExerciseId: $set->workout_block_exercise_id,
            exerciseName: $exerciseName,
            setIndex: $set->set_index,
            groupType: $groupType->value,
            targetWeightKg: round($targetWeightG / 1000, 3),
            targetReps: $groupType === SetGroupType::WarmUp
                ? ($warmUpStep?->reps)
                : ($groupType === SetGroupType::Working ? $prescribedReps : null),
            loggedWeightKg: $set->weight_g !== null ? round($set->weight_g / 1000, 3) : null,
            loggedReps: $set->reps,
            completed: $set->completed_at !== null,
            restSeconds: $restSeconds,
        );
    }
}
