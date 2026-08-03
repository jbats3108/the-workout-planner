<?php

namespace App\Users\Data;

use App\Routines\Data\Editor\SyncWarmUpStepData;
use App\Users\Enums\BumpWhen;
use App\Users\Enums\WarmUpDefaultsScope;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class UpdateTrainingDefaultsData extends Data
{
    /**
     * @param  DataCollection<int, SyncWarmUpStepData>|null  $warmUpStepsDefault
     */
    public function __construct(
        /** null means empty list from the form. */
        #[DataCollectionOf(SyncWarmUpStepData::class)]
        public readonly ?DataCollection $warmUpStepsDefault = null,

        #[Enum(WarmUpDefaultsScope::class)]
        public readonly WarmUpDefaultsScope $warmUpDefaultsScope = WarmUpDefaultsScope::AllBlocks,

        #[Nullable, Min(1), Max(100)]
        public readonly ?int $achievementFloorDefault = null,

        #[Enum(BumpWhen::class)]
        public readonly BumpWhen $bumpWhenDefault = BumpWhen::AnySet,

        #[Min(0), Max(5)]
        public readonly float $deloadWeightFactorDefault = 0.5,

        #[Min(0), Max(10)]
        public readonly float $deloadRepsFactorDefault = 2.0,

        #[Min(0), Max(99)]
        public readonly int $deloadEveryNDefault = 3,
    ) {}
}
