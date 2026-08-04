<?php

namespace App\Shared\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreInviteRequestData extends Data
{
    public function __construct(
        #[Max(255)]
        public readonly string $name,

        #[Email, Max(255)]
        public readonly string $email,

        #[Nullable, Max(5000)]
        public readonly ?string $message = null,
    ) {}
}
