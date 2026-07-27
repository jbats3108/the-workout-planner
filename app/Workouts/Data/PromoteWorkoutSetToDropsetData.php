<?php

namespace App\Workouts\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class PromoteWorkoutSetToDropsetData extends Data
{
    /**
     * @param  DataCollection<int, CompleteWorkoutSetSegmentData>  $segments
     */
    public function __construct(
        #[DataCollectionOf(CompleteWorkoutSetSegmentData::class)]
        #[Min(2), Max(20)]
        public readonly DataCollection $segments,
    ) {}

    /**
     * @return list<int>
     */
    public function segmentWeightGrams(): array
    {
        return array_map(
            static fn (CompleteWorkoutSetSegmentData $segment): int => $segment->weightGrams(),
            array_values($this->segments->all()),
        );
    }
}
