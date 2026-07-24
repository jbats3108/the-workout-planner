<?php

namespace App\DataTransferObjects\Exercises;

use App\Models\Exercise;
use Spatie\LaravelData\Data;

final class ExerciseData extends Data
{
    private function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $primaryMuscleGroup,
        public readonly ?string $secondaryMuscleGroup,
    ) {}

    public static function fromExercise(Exercise $exercise): ExerciseData
    {
        return new self(
            $exercise->getName(),
            $exercise->getSlug(),
            $exercise->primaryMuscleGroup->getName(),
            $exercise->secondaryMuscleGroup?->getName(),
        );
    }
}
