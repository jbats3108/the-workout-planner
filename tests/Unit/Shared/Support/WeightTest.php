<?php

namespace Tests\Unit\Shared\Support;

use App\Shared\Support\Weight;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WeightTest extends TestCase
{
    #[Test]
    #[DataProvider('kgToGramsProvider')]
    public function it_converts_kilograms_to_grams(float $kilograms, int $expectedGrams): void
    {
        $this->assertSame($expectedGrams, Weight::kgToGrams($kilograms));
    }

    #[Test]
    #[DataProvider('gramsToKgProvider')]
    public function it_converts_grams_to_kilograms(int $grams, float $expectedKilograms): void
    {
        $this->assertSame($expectedKilograms, Weight::gramsToKg($grams));
    }

    /**
     * @return list<array{0: float, 1: int}>
     */
    public static function kgToGramsProvider(): array
    {
        return [
            [80.0, 80000],
            [28.75, 28750],
            [0.001, 1],
            [0.0004, 0],
            [0.0005, 1],
        ];
    }

    /**
     * @return list<array{0: int, 1: float}>
     */
    public static function gramsToKgProvider(): array
    {
        return [
            [80000, 80.0],
            [28750, 28.75],
            [1, 0.001],
            [0, 0.0],
        ];
    }
}
