<?php

namespace App\Routines\Data\Editor;

use App\Exercises\Models\Exercise;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncBlockExerciseData extends Data
{
    public function __construct(
        #[Exists(Exercise::class, 'id')]
        public readonly int $exerciseId,

        #[Min(0)]
        public readonly float $workingWeightKg,

        #[Min(1), Max(100)]
        public readonly int $prescribedReps,

        #[Min(1), Max(100)]
        public readonly ?int $achievementFloor = null,

        #[Min(1), Max(100)]
        public readonly ?int $progressionTarget = null,
    ) {}

    public function workingWeightGrams(): int
    {
        return (int) round($this->workingWeightKg * 1000);
    }
}
