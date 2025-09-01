<?php

namespace App\DataTransferObjects\Exercises;

use App\DataTransferObjects\Casts\SlugToModelCast;
use App\Enums\Difficulty;
use App\Enums\MovementType;
use App\Models\MuscleGroup;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreExerciseData extends Data
{
    /** @param array<int, string> $equipment */
    public function __construct(
        public readonly string $name,
        public readonly string $slug,

        #[Exists(MuscleGroup::class, 'slug')]
        #[WithCast(SlugToModelCast::class, MuscleGroup::class)]
        public readonly MuscleGroup $primaryMuscleGroup,
        public readonly MovementType $movementType,
        public readonly Difficulty $difficulty,
        public readonly array $equipment,

        #[Different('primaryMuscleGroup')]
        #[Exists(MuscleGroup::class, 'slug')]
        #[WithCast(SlugToModelCast::class, MuscleGroup::class)]
        public readonly ?MuscleGroup $secondaryMuscleGroup = null,
    ) {}
}
