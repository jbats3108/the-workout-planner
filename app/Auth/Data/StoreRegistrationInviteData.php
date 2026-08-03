<?php

namespace App\Auth\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreRegistrationInviteData extends Data
{
    public function __construct(
        #[Email, Max(255)]
        public readonly string $email,

        #[In('user', 'admin')]
        public readonly string $role = 'user',

        #[Nullable, Max(255)]
        public readonly ?string $note = null,

        #[Nullable, Min(1), Max(365)]
        public readonly ?int $expiresInDays = 7,
    ) {}
}
