<?php

namespace App\Routines\Data\Editor;

use App\Shared\Data\WeightKgSegmentData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncDropsetData extends Data
{
    /**
     * @param  DataCollection<int, WeightKgSegmentData>  $segments
     */
    public function __construct(
        #[Min(0), Max(19)]
        public readonly int $setIndex,

        #[DataCollectionOf(WeightKgSegmentData::class)]
        #[Min(2), Max(20)]
        public readonly DataCollection $segments,
    ) {}
}
