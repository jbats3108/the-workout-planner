<?php

namespace App\MuscleGroups\Data;

use App\MuscleGroups\Models\MuscleGroup;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

class StoreMuscleGroupData extends Data
{
    public function __construct(
        #[Unique(MuscleGroup::class, 'name')]
        public readonly string $name,
        #[Unique(MuscleGroup::class, 'slug')]
        public readonly string $slug
    ) {}
}
