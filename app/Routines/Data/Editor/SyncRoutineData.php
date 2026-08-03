<?php

namespace App\Routines\Data\Editor;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncRoutineData extends Data
{
    /**
     * @param  DataCollection<int, SyncRoutineBlockData>|null  $blocks
     */
    public function __construct(
        #[Max(255)]
        public readonly string $name,

        #[Min(0), Max(5)]
        public readonly ?float $deloadWeightFactor = null,

        #[Min(0), Max(10)]
        public readonly ?float $deloadRepsFactor = null,

        #[Min(0), Max(99)]
        public readonly ?int $deloadEveryN = null,

        #[DataCollectionOf(SyncRoutineBlockData::class)]
        public readonly ?DataCollection $blocks = null,

        public readonly ?string $expectedUpdatedAt = null,
    ) {}
}
