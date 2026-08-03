<?php

namespace App\Workouts\Data;

use App\Shared\Data\WeightKgSegmentData;
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
     * @param  DataCollection<int, WeightKgSegmentData>  $segments
     */
    public function __construct(
        #[DataCollectionOf(WeightKgSegmentData::class)]
        #[Min(2), Max(20)]
        public readonly DataCollection $segments,
    ) {}

    /**
     * @return list<int>
     */
    public function segmentWeightGrams(): array
    {
        return WeightKgSegmentData::gramsList($this->segments);
    }
}
