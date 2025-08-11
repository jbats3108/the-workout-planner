<?php

namespace Feature\Controllers\Exercises;

use App\Models\Exercise;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteExerciseControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rejects_requests_from_non_admins(): void
    {
        // Given
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('user');

        $exercise = Exercise::factory()->create();

        $route = route('exercises.delete', ['exercise' => $exercise->id]);

        // When
        $response = $this->actingAs($user)->delete($route);

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function it_deletes_an_exercise(): void
    {
        // Given
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $exercise = Exercise::factory()->create();

        $route = route('exercises.delete', ['exercise' => $exercise->id]);

        // When
        $response = $this->actingAs($user)->delete($route);

        // Then
        $response->assertRedirect();
        $this->assertSoftDeleted(Exercise::class, ['id' => $exercise->id]);

    }
}
