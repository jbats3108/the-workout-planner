<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Routines;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

class AddRoutineExerciseData extends Data
{
    public function __construct(
        public readonly int $sets = 3,
        public readonly int $reps = 6,

        #[MapName(SnakeCaseMapper::class)]
        public readonly bool $toFailure = false
    ) {}
}
