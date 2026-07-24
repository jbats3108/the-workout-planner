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
     * @param  list<int>|null  $percents
     */
    public function __construct(
        #[Min(0), Max(20)]
        public readonly int $setCount = 0,

        #[Min(0), Max(3600)]
        public readonly int $restSeconds = 60,

        /** Empty list is valid (no warm-up steps). */
        public readonly ?array $percents = null,
    ) {}

    /**
     * @return list<int>
     */
    public function percentList(): array
    {
        return array_values(array_filter(
            $this->percents ?? [],
            fn (mixed $p): bool => is_int($p) ? $p > 0 : (is_numeric($p) && (int) $p > 0)
        ));
    }
}
