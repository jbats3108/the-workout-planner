<?php

namespace App\Users\Data;

use App\Users\Models\User;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class SharedUserData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?CarbonInterface $emailVerifiedAt,
        public readonly CarbonInterface $createdAt,
        public readonly CarbonInterface $updatedAt,
        public readonly bool $isAdmin,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            emailVerifiedAt: $user->email_verified_at,
            createdAt: $user->created_at,
            updatedAt: $user->updated_at,
            isAdmin: $user->isAdmin(),
        );
    }
}
