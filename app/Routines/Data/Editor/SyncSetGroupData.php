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
class SyncSetGroupData extends Data
{
    /**
     * @param  DataCollection<int, SyncDropsetData>|null  $dropsets
     */
    public function __construct(
        #[Min(1), Max(20)]
        public readonly int $setCount,

        #[Min(0), Max(3600)]
        public readonly int $restSeconds,

        #[DataCollectionOf(SyncDropsetData::class)]
        public readonly ?DataCollection $dropsets = null,
    ) {}

    /**
     * @return list<SyncDropsetData>
     */
    public function dropsetList(): array
    {
        if ($this->dropsets === null) {
            return [];
        }

        return array_values($this->dropsets->all());
    }
}
