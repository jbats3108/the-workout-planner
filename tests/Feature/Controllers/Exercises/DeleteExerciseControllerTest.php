<?php

namespace Feature\Controllers\Exercises;

use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class DeleteExerciseControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_rejects_requests_from_non_admins(): void
    {
        // Given
        $exercise = Exercise::factory()->create();

        $route = route('exercises.delete', ['exercise' => $exercise->id]);

        // When
        $response = $this->actingAs($this->user)->delete($route);

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function it_deletes_an_exercise(): void
    {
        // Given
        $exercise = Exercise::factory()->create();

        $route = route('exercises.delete', ['exercise' => $exercise->id]);

        // When
        $response = $this->actingAs($this->adminUser)->delete($route);

        // Then
        $response->assertRedirect();
        $this->assertSoftDeleted(Exercise::class, ['id' => $exercise->id]);

    }
}
