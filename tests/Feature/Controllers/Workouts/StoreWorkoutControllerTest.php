<?php

namespace Tests\Feature\Controllers\Workouts;

use App\Models\Exercise;
use App\Models\Routine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreWorkoutControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_requires_the_routine_to_belong_to_the_user(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        // When
        $response = $this->actingAs($this->secondUser)->post(route('workout.store', ['routine' => $routine->id]));

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function it_requires_the_routine_to_have_at_least_one_exercise(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        // When
        $response = $this->actingAs($this->user)->post(route('workout.store', ['routine' => $routine->id]));

        // Then
        $response->assertRedirectBack();
        $response->assertRedirectBackWithErrors();
    }

    #[Test]
    public function it_creates_a_workout_from_the_provided_routine(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();
        $exercise = Exercise::factory()->create();

        $routine->exercises()->save($exercise);

        // When
        $response = $this->actingAs($this->user)->post(route('workout.store', ['routine' => $routine->id]));

        // Then
        $response->assertCreated();

        $this->assertDatabaseHas('workouts', [
            'routine_id' => $routine->id,
        ]);

    }
}
