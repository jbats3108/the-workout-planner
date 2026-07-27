<?php

namespace App\Users\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncPlateProfileBarData extends Data
{
    public function __construct(
        #[Max(100)]
        public readonly string $name,

        #[Min(0), Max(100000)]
        public readonly int $weightG,

        public readonly bool $isDefault = false,
    ) {}
}
