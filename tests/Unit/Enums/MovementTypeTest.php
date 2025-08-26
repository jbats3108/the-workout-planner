<?php

namespace Tests\Unit\Enums;

use App\Enums\MovementType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MovementTypeTest extends TestCase
{
    #[Test]
    public function it_returns_the_values_for_all_cases(): void
    {
        // Given
        $expectedValues = [
            'push',
            'pull',
            'lower',
        ];

        // Then
        $this->assertSame($expectedValues, MovementType::values());

    }
}
