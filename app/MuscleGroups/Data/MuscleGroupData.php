<?php

declare(strict_types=1);

namespace App\MuscleGroups\Data;

use Spatie\LaravelData\Data;

class MuscleGroupData extends Data
{
    public function __construct(public readonly string $name, public readonly string $slug) {}
}
