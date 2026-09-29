<?php

namespace App\Routines\Data;

use App\ExerciseProfiles\Models\ExerciseProfile;
use App\Shared\Data\Validation\DeloadEveryN;
use App\Users\Enums\ProgressionStyle;
use App\Users\Enums\ProgressiveMidBlock;
use App\Users\Models\User;
use Spatie\LaravelData\Attributes\FromAuthenticatedUser;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreRoutineData extends Data
{
    public function __construct(
        #[Max(255)]
        public readonly string $name,

        #[Exists(ExerciseProfile::class, 'id')]
        public readonly int $defaultExerciseProfileId,

        #[FromAuthenticatedUser]
        public readonly User $user,

        #[DeloadEveryN]
        public readonly ?int $deloadEveryN = null,

        #[Enum(ProgressionStyle::class)]
        public readonly ?ProgressionStyle $progressionStyle = null,

        #[Enum(ProgressiveMidBlock::class)]
        public readonly ?ProgressiveMidBlock $progressiveMidBlock = null,
    ) {}
}
