<?php

namespace App\Shared\Data;

use App\Shared\Enums\FeedbackCategory;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class StoreFeedbackData extends Data
{
    public function __construct(
        public readonly FeedbackCategory $category,

        #[Max(255)]
        public readonly string $name,

        #[Email, Max(255)]
        public readonly string $email,

        #[Min(1), Max(5000)]
        public readonly string $message,
    ) {}
}
