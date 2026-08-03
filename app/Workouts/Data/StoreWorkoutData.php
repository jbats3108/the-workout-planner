<?php

namespace App\Workouts\Data;

use App\Workouts\Enums\WorkoutMode;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreWorkoutData extends Data
{
    public function __construct(
        #[Enum(WorkoutMode::class)]
        public readonly ?WorkoutMode $mode = null,
    ) {}

    public function modeOrDefault(): WorkoutMode
    {
        return $this->mode ?? WorkoutMode::Standard;
    }
}
