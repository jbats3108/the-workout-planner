<?php

namespace App\Workouts\Data\Progression;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ApplyBumpsData extends Data
{
    /**
     * @param  list<int>  $routineBlockExerciseIds
     * @param  list<int>  $undoBumpRecordIds
     */
    public function __construct(
        public readonly array $routineBlockExerciseIds = [],
        public readonly array $undoBumpRecordIds = [],
    ) {}
}
