<?php

namespace App\DataTransferObjects\Routines;

use App\Models\Routine;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class RoutineData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?float $deloadWeightFactor = null,
        public readonly ?float $deloadRepsFactor = null,
    ) {}

    public static function fromRoutine(Routine $routine): RoutineData
    {
        return new self(
            $routine->getName(),
            $routine->deload_weight_factor !== null ? (float) $routine->deload_weight_factor : null,
            $routine->deload_reps_factor !== null ? (float) $routine->deload_reps_factor : null,
        );
    }
}
