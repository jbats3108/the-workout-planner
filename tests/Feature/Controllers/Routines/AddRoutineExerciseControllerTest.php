<?php

namespace Tests\Feature\Controllers\Routines;

use App\Models\Exercise;
use App\Models\Routine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class AddRoutineExerciseControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function admins_cannot_add_exercises_to_a_users_routine(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        $exercise = Exercise::factory()->create();

        $request = [
            'exercise' => $exercise->id,
        ];

        // When
        $response = $this->actingAs($this->adminUser)->post(route('routines.add-exercise', [$routine, $exercise]), $request);

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function users_can_only_add_exercises_to_their_own_routines(): void
    {
        // Given
        $routine = Routine::factory()->withOwner($this->user)->create();

        $exercise = Exercise::factory()->create();

        $request = [
            'exercise' => $exercise->id,
        ];

        // When
        $response = $this->actingAs($this->secondUser)->post(route('routines.add-exercise', [$routine, $exercise]), $request);

        // Then
        $response->assertForbidden();

    }

    #[Test]
    public function it_adds_an_exercise_to_a_routine(): void
    {
        $routine = Routine::factory()->withOwner($this->user)->create();

        $exercise = Exercise::factory()->create();

        // When
        $response = $this->actingAs($this->user)->post(route('routines.add-exercise', [$routine, $exercise]));

        // Then
        $response->assertOk();

        $routine->refresh();

        $this->assertCount(1, $routine->exercises);

        $this->assertTrue($routine->exercises->contains($exercise));

        // Default values
        $this->assertDatabaseHas('routine_exercise', [
            'exercise_id' => $exercise->id,
            'routine_id' => $routine->id,
            'weight' => 10,
            'to_failure' => false,
            'sets' => 3,
            'reps' => 6,
        ]);

    }

    #[Test]
    public function it_adds_an_exercise_with_non_default_properties(): void
    {
        $routine = Routine::factory()->withOwner($this->user)->create();

        $exercise = Exercise::factory()->create();

        $request = [
            'reps' => 12,
            'sets' => 5,
            'weight' => 11.45,
            'to_failure' => true,
        ];

        // When
        $response = $this->actingAs($this->user)->post(route('routines.add-exercise', [$routine, $exercise]), $request);

        // Then
        $response->assertOk();

        $this->assertDatabaseHas('routine_exercise', [
            'exercise_id' => $exercise->id,
            'routine_id' => $routine->id,
            'weight' => 11.45,
            'to_failure' => true,
            'sets' => 5,
            'reps' => 12,
        ]);

    }
}
