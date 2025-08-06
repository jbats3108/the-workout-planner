<?php
declare(strict_types=1);

namespace App\Enums\Traits;

use BackedEnum;

trait HasValues
{
    public static function values(): array
    {
        return array_map(fn(BackedEnum $case) => $case->value, static::cases());
    }
}