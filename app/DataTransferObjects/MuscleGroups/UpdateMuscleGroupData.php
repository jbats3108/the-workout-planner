<?php

namespace App\DataTransferObjects\MuscleGroups;

use Spatie\LaravelData\Data;

class UpdateMuscleGroupData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
    ) {}
}
