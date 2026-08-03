<?php

namespace App\Routines\Data\Editor;

use App\Shared\Support\Weight;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncDropsetSegmentData extends Data
{
    public function __construct(
        #[Min(0)]
        public readonly float $weightKg,
    ) {}

    public function weightGrams(): int
    {
        return Weight::kgToGrams($this->weightKg);
    }
}
