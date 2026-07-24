<?php

namespace App\Users\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class UpsertPlateProfileData extends Data
{
    /**
     * @param  DataCollection<int, SyncPlateProfileBarData>  $bars
     * @param  DataCollection<int, SyncPlateProfilePlateData>  $plates
     */
    public function __construct(
        #[Max(100)]
        public readonly string $name,

        #[DataCollectionOf(SyncPlateProfileBarData::class)]
        public readonly DataCollection $bars,

        #[DataCollectionOf(SyncPlateProfilePlateData::class)]
        public readonly DataCollection $plates,
    ) {}
}
