<?php

namespace App\DataTransferObjects\RoutineTypes;

use App\Models\RoutineType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

class StoreRoutineTypeData extends Data
{
    public function __construct(
        #[Unique(RoutineType::class, 'name')]
        public readonly string $name,
        #[Unique(RoutineType::class, 'slug')]
        public readonly string $slug,
    ) {}
}
