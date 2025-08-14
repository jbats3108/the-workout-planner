<?php

namespace App\DataTransferObjects\Routines;

use App\Models\Routine;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class RoutineData extends Data
{
    private function __construct(
        public readonly string $name,
        public readonly string $routineType,
        public string $ownerName,
    ) {}

    public static function fromRoutine(Routine $routine): static
    {
        return new static(
            $routine->getName(),
            $routine->routineType->getName(),
            $routine->owner->name,
        );
    }
}
