<?php

namespace App\Exercises\Data;

use App\Shared\Data\Casts\SlugToModelCast;
use App\MuscleGroups\Models\MuscleGroup;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreExerciseData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,

        #[Exists(MuscleGroup::class, 'slug')]
        #[WithCast(SlugToModelCast::class, MuscleGroup::class)]
        public readonly MuscleGroup $primaryMuscleGroup,

        #[Different('primaryMuscleGroup')]
        #[Exists(MuscleGroup::class, 'slug')]
        #[WithCast(SlugToModelCast::class, MuscleGroup::class)]
        public readonly ?MuscleGroup $secondaryMuscleGroup = null,
    ) {}
}
