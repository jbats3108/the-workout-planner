<?php

namespace App\Users\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncPlateProfilePlateData extends Data
{
    public function __construct(
        #[Min(1), Max(100000)]
        public readonly int $denominationG,

        #[Min(0), Max(100)]
        public readonly int $count,

        #[Max(32)]
        public readonly ?string $colour = null,
    ) {}
}
