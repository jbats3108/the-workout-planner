<?php

namespace App\Routines\Data\Editor;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncWarmUpData extends Data
{
    /**
     * @param  list<int>  $percents
     */
    public function __construct(
        #[Min(0), Max(20)]
        public readonly int $setCount = 0,

        #[Min(0), Max(3600)]
        public readonly int $restSeconds = 60,

        public readonly array $percents = [],
    ) {}
}
