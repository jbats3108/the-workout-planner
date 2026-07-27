<?php

namespace App\Workouts\Data\History;

use App\Users\Models\User;
use App\Workouts\Data\Player\WorkoutPlayerPageData;
use App\Workouts\Models\Workout;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class HistoryDetailPageData extends Data
{
    public function __construct(
        public WorkoutPlayerPageData $workout,
        public bool $canReEvaluate,
    ) {}

    public static function fromWorkout(Workout $workout, User $user): self
    {
        return new self(
            workout: WorkoutPlayerPageData::fromWorkout($workout, $user),
            canReEvaluate: $workout->isEligibleForProgressionReEval(),
        );
    }
}
