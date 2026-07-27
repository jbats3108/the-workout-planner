<?php

namespace App\Workouts\Data\Progression;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProgressionSessionData extends Data
{
    /**
     * @param  DataCollection<int, BumpProposalData>  $bumps
     * @param  DataCollection<int, UndoBumpProposalData>  $undos
     */
    public function __construct(
        #[DataCollectionOf(BumpProposalData::class)]
        public DataCollection $bumps,
        #[DataCollectionOf(UndoBumpProposalData::class)]
        public DataCollection $undos,
    ) {}

    public function hasActions(): bool
    {
        return $this->bumps->count() > 0 || $this->undos->count() > 0;
    }
}
