<?php

namespace Tests\Unit\Traits;

use App\Traits\HasSlug;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mock class to test HasName trait
 */
class HasSlugMock
{
    public string $slug;

    use HasSlug;
}

class HasSlugTest extends TestCase
{

    #[Test]
    public function it_retrieves_the_slug(): void
    {
        // Given
        $class = new HasSlugMock();

        $class->slug = 'test';

        // When
        $returnedSlug = $class->getSlug();

        // Then
        $this->assertSame('test', $returnedSlug);

    }
}
