<?php

namespace Tests\Feature\Exercises\Http\Controllers;

use App\Exercises\Models\Exercise;
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
        $this->seedUsers(false);
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
        $exercise = Exercise::factory()->create(['user_id' => null]);

        $route = route('exercises.delete', ['exercise' => $exercise->id]);

        // When
        $response = $this->actingAs($this->adminUser)->delete($route);

        // Then
        $response->assertRedirect(route('admin.exercises'));
        $this->assertSoftDeleted(Exercise::class, ['id' => $exercise->id]);
    }

    #[Test]
    public function it_rejects_deleting_a_custom_user_exercise(): void
    {
        // Given
        $exercise = Exercise::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $route = route('exercises.delete', ['exercise' => $exercise->id]);

        // When
        $response = $this->actingAs($this->adminUser)->delete($route);

        // Then
        $response->assertForbidden();
        $this->assertDatabaseHas(Exercise::class, [
            'id' => $exercise->id,
            'deleted_at' => null,
        ]);
    }
}
