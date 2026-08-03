<?php

namespace App\Shared\Support;

final class Weight
{
    public static function kgToGrams(float $kilograms): int
    {
        return (int) round($kilograms * 1000);
    }

    public static function gramsToKg(int $grams): float
    {
        return round($grams / 1000, 3);
    }
}
