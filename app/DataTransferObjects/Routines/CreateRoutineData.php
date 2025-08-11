<?php

namespace App\DataTransferObjects\Routines;

use App\DataTransferObjects\Casts\SlugToModelCast;
use App\Models\RoutineType;
use App\Models\User;
use Spatie\LaravelData\Attributes\FromAuthenticatedUser;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CreateRoutineData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,

        #[Exists(RoutineType::class, 'slug')]
        #[WithCast(SlugToModelCast::class, RoutineType::class)]
        public readonly RoutineType $routineType,

        #[FromAuthenticatedUser]
        public readonly User $owner,
    ) {}
}
