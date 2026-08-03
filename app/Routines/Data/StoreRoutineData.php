<?php

namespace App\Routines\Data;

use App\Users\Models\User;
use Spatie\LaravelData\Attributes\FromAuthenticatedUser;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreRoutineData extends Data
{
    public function __construct(
        #[Max(255)]
        public readonly string $name,

        #[FromAuthenticatedUser]
        public readonly User $user,

        #[Min(0), Max(5)]
        public readonly ?float $deloadWeightFactor = null,

        #[Min(0), Max(10)]
        public readonly ?float $deloadRepsFactor = null,

        #[Min(0), Max(99)]
        public readonly ?int $deloadEveryN = null,
    ) {}
}
