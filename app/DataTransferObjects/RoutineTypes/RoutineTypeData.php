<?php

namespace App\DataTransferObjects\RoutineTypes;

use Spatie\LaravelData\Data;

class RoutineTypeData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
    ) {}
}
