<?php

namespace Tests\Unit\Traits;

use App\Traits\HasName;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mock class to test HasName trait
 */
class HasNameMock
{
    public string $name;

    use HasName;
}

class HasNameTest extends TestCase
{
    #[Test]
    public function it_retrieves_the_name(): void
    {
        // Given
        $class = new HasNameMock;

        $class->name = 'Test';

        // When
        $returnedName = $class->getName();

        // Then
        $this->assertSame('Test', $returnedName);

    }
}
