<?php

namespace App\DataTransferObjects\Exercises;

use App\Models\Exercise;
use Spatie\LaravelData\Data;

class ExerciseData extends Data
{
    private function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $primaryMuscleGroup,
        public readonly ?string $secondaryMuscleGroup,
        public readonly string $movementType,
        public readonly string $difficulty,
        public readonly array $equipment,
    ) {}

    public static function fromExercise(Exercise $exercise): static
    {
        return new static(
            $exercise->getName(),
            $exercise->getSlug(),
            $exercise->primaryMuscleGroup->getName(),
            $exercise->secondaryMuscleGroup?->getName(),
            $exercise->movementType()->name,
            $exercise->difficulty->name,
            $exercise->equipment ?? [],
        );
    }
}
