<?php

namespace App\Users\Data;

use App\Routines\Data\Editor\SyncWarmUpStepData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
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
    ) {}
}
