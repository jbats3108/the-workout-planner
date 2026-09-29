<?php

namespace App\Routines\Data\Editor;

use App\ExerciseProfiles\Models\ExerciseProfile;
use App\Shared\Data\Validation\DeloadEveryN;
use App\Users\Enums\ProgressionStyle;
use App\Users\Enums\ProgressiveMidBlock;
use Override;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncRoutineData extends Data
{
    /**
     * @param  DataCollection<int, SyncRoutineBlockData>|null  $blocks
     */
    public function __construct(
        #[Max(255)]
        public readonly string $name,

        #[DeloadEveryN]
        public readonly ?int $deloadEveryN = null,

        #[Enum(ProgressionStyle::class)]
        public readonly ?ProgressionStyle $progressionStyle = null,

        #[Enum(ProgressiveMidBlock::class)]
        public readonly ?ProgressiveMidBlock $progressiveMidBlock = null,

        #[Exists(ExerciseProfile::class, 'id')]
        public readonly ?int $defaultExerciseProfileId = null,

        #[DataCollectionOf(SyncRoutineBlockData::class)]
        public readonly ?DataCollection $blocks = null,

        public readonly ?string $expectedUpdatedAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    #[Override]
    public static function prepareForPipeline(array $properties): array
    {
        return BlankRestSeconds::inRoutinePayload($properties);
    }
}
