<?php

namespace App\Workouts\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CompleteWorkoutSetSegmentData extends Data
{
    public function __construct(
        #[Min(0)]
        public readonly float $weightKg,
    ) {}

    public function weightGrams(): int
    {
        return (int) round($this->weightKg * 1000);
    }
}
