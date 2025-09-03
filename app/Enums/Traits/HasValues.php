<?php

declare(strict_types=1);

namespace App\Enums\Traits;

use BackedEnum;

trait HasValues
{
    /** @return array<int, string> | array<int, int> */
    public static function values(): array
    {
        return array_map(fn (BackedEnum $case): int|string => $case->value, static::cases());
    }
}
