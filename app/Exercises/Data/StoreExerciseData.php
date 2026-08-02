<?php

namespace App\Exercises\Data;

use App\Exercises\Enums\ExerciseEquipment;
use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use App\Shared\Data\Casts\SlugToModelCast;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;

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

        #[Enum(ExerciseEquipment::class)]
        public readonly ?ExerciseEquipment $equipment = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'slug' => [
                Rule::unique(Exercise::class, 'slug')->whereNull('user_id'),
            ],
        ];
    }
}
