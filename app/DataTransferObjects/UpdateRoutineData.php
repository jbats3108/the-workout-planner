<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class UpdateRoutineData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,
    ) {}
}
