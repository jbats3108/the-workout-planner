<?php

namespace App\DataTransferObjects\Routines;

use App\Models\Routine;
use Spatie\LaravelData\Data;

class RoutineData extends Data
{
    private function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $routineType,
        public string $ownerName,
    ) {}

    public static function fromRoutine(Routine $routine): static
    {
        return new static(
            $routine->getName(),
            $routine->getSlug(),
            $routine->routineType->getName(),
            $routine->owner->name,
        );
    }
}
