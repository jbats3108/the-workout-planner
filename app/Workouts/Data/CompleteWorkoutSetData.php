<?php

namespace App\Workouts\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CompleteWorkoutSetData extends Data
{
    /**
     * @param  DataCollection<int, CompleteWorkoutSetSegmentData>|null  $segments
     */
    public function __construct(
        #[Min(0), Max(100)]
        public readonly int $reps,

        #[Nullable, Min(0)]
        #[RequiredWithout('segments')]
        public readonly ?float $weightKg = null,

        #[Nullable]
        #[DataCollectionOf(CompleteWorkoutSetSegmentData::class)]
        #[Min(2), Max(20)]
        public readonly ?DataCollection $segments = null,
    ) {}

    public function weightGrams(): ?int
    {
        if ($this->weightKg === null) {
            return null;
        }

        return (int) round($this->weightKg * 1000);
    }

    /**
     * @return list<int>|null
     */
    public function segmentWeightGrams(): ?array
    {
        if ($this->segments === null) {
            return null;
        }

        return array_map(
            static fn (CompleteWorkoutSetSegmentData $segment): int => $segment->weightGrams(),
            array_values($this->segments->all()),
        );
    }
}
