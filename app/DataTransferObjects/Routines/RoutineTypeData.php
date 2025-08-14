<?php

namespace App\DataTransferObjects\Routines;

use App\Models\RoutineType;
use Spatie\LaravelData\Data;

class RoutineTypeData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug
    ) {}

    public static function fromModel(RoutineType $routineType): static
    {
        return new static(
            $routineType->getName(),
            $routineType->getSlug()
        );
    }
}
