<?php

namespace App\Workouts\Data\History;

use App\Workouts\Models\Workout;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class HistoryWorkoutItemData extends Data
{
    public function __construct(
        public string $id,
        public string $routineName,
        public int $routineId,
        public string $mode,
        public string $finishedAt,
    ) {}

    public static function fromWorkout(Workout $workout): self
    {
        $workout->loadMissing('routine');

        return new self(
            id: $workout->ulid,
            routineName: $workout->routine->getName(),
            routineId: $workout->routine_id,
            mode: $workout->mode->value,
            finishedAt: $workout->finished_at?->toIso8601String() ?? '',
        );
    }
}
