<?php

namespace App\Routines\Data;

use App\Users\Models\User;
use Spatie\LaravelData\Attributes\FromAuthenticatedUser;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreRoutineData extends Data
{
    public function __construct(
        public readonly string $name,

        #[FromAuthenticatedUser]
        public readonly User $user,

        public readonly ?float $deloadWeightFactor = null,
        public readonly ?float $deloadRepsFactor = null,
    ) {}
}
