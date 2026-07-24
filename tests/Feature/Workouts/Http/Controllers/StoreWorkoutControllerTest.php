<?php

namespace Tests\Feature\Workouts\Http\Controllers;

use App\Shared\Enums\SetGroupType;
use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
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
        $routine = Routine::factory()->withUser($this->user)->create();

        // When
        $response = $this->actingAs($this->secondUser)->post(route('workout.store', ['routine' => $routine->id]));

        // Then
        $response->assertForbidden();
    }

    #[Test]
    public function it_requires_the_routine_to_have_at_least_one_exercise(): void
    {
        // Given
        $routine = Routine::factory()->withUser($this->user)->create();

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
        $routine = Routine::factory()->withUser($this->user)->create();
        $this->seedRoutineBlockWithExercise($routine);

        // When
        $response = $this->actingAs($this->user)->post(route('workout.store', ['routine' => $routine->id]));

        // Then
        $response->assertCreated();

        $this->assertDatabaseHas('workouts', [
            'routine_id' => $routine->id,
            'user_id' => $this->user->id,
        ]);
    }

    private function seedRoutineBlockWithExercise(Routine $routine): void
    {
        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);

        RoutineBlockExercise::create([
            'routine_block_id' => $block->id,
            'exercise_id' => Exercise::factory()->create()->id,
            'position' => 1,
            'working_weight_g' => 80000,
            'prescribed_reps' => 6,
        ]);

        RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::Working,
            'set_count' => 3,
        ]);
    }
}
