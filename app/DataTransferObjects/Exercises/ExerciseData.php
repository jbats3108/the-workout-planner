<?php /** @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection */

namespace App\DataTransferObjects\Exercises;

use App\Models\Exercise;
use Spatie\LaravelData\Data;

final class ExerciseData extends Data
{
    /** @param array<string> | mixed[] $equipment */
    private function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $primaryMuscleGroup,
        public readonly ?string $secondaryMuscleGroup,
        public readonly string $movementType,
        public readonly string $difficulty,
        public readonly array $equipment,
    ) {}

    public static function fromExercise(Exercise $exercise): ExerciseData
    {

        return new self(
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
