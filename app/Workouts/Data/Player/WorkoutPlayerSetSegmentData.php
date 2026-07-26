<?php

namespace App\Workouts\Data\Player;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class WorkoutPlayerSetSegmentData extends Data
{
    public function __construct(
        public readonly int $position,
        public readonly float $weightKg,
    ) {}
}
