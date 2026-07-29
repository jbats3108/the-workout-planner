<?php

namespace App\Workouts\Data\Progression;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProgressionPageData extends Data
{
    /**
     * @param  DataCollection<int, BumpProposalData>  $bumps
     * @param  DataCollection<int, UndoBumpProposalData>  $undos
     */
    public function __construct(
        public string $workoutId,
        public string $routineName,
        #[DataCollectionOf(BumpProposalData::class)]
        public DataCollection $bumps,
        #[DataCollectionOf(UndoBumpProposalData::class)]
        public DataCollection $undos = new DataCollection(UndoBumpProposalData::class, []),
    ) {}
}
