<?php

namespace Tests\Unit\Traits;

use App\Models\Exercise;
use App\Traits\HasSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mock class to test HasSlug trait
 */
class HasSlugMock
{
    public string $slug;

    use HasSlug;
}

class HasSlugTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_retrieves_the_slug(): void
    {
        // Given
        $class = new HasSlugMock;

        $class->slug = 'test';

        // When
        $returnedSlug = $class->getSlug();

        // Then
        $this->assertSame('test', $returnedSlug);

    }

    #[Test]
    public function it_looks_up_the_model_based_on_the_slug(): void
    {
        // Given
        $model = Exercise::factory()->create([
            'slug' => 'test',
        ]);

        // When
        $lookup = Exercise::lookup('test');

        // Then
        $this->assertTrue($lookup->is($model));
    }

    #[Test]
    public function it_returns_null_if_there_is_no_matching_model_for_the_slug(): void
    {
        // Given
        $model = Exercise::factory()->create([
            'slug' => 'test',
        ]);

        // When
        $lookup = Exercise::lookup('not-test');

        // Then
        $this->assertNull($lookup);
    }
}
