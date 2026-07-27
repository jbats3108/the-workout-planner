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
     */
    public function __construct(
        public int $workoutId,
        public string $routineName,
        #[DataCollectionOf(BumpProposalData::class)]
        public DataCollection $bumps,
    ) {}
}
