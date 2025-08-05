<?php

namespace App\Enums;

enum MovementType: string
{
    case PUSH  = 'push';
    case PULL  = 'pull';
    case LOWER = 'lower';

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
