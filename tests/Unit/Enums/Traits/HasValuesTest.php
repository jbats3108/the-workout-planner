<?php

namespace Tests\Unit\Enums\Traits;

use App\Enums\Traits\HasValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

enum DummyStringEnum: string
{
    use HasValues;

    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}

enum DummyIntEnum: int
{
    use HasValues;

    case FOO = 1;
    case BAR = 2;
    case BAZ = 3;
}

class HasValuesTest extends TestCase
{
    #[Test]
    public function it_returns_the_values_for_the_string_backed_enum(): void
    {
        // Given
        $expectedValues = [
            'foo',
            'bar',
            'baz',
        ];

        $this->assertSame($expectedValues, DummyStringEnum::values());

    }

    #[Test]
    public function it_returns_the_values_for_the_int_backed_enum(): void
    {
        // Given
        $expectedValues = [1, 2, 3];

        $this->assertSame($expectedValues, DummyIntEnum::values());

    }
}
