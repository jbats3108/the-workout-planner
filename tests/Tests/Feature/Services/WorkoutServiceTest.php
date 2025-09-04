<?php

namespace Tests\Feature\Services;

use App\Exceptions\WorkoutServiceException;
use App\Models\Exercise;
use App\Models\Routine;
use App\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkoutService $workoutService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workoutService = new WorkoutService;
    }

    #[Test]
    public function it_throw_an_exception_if_it_tries_to_create_a_workout_from_a_routine_with_no_exercises(): void
    {
        // Given
        $routine = Routine::factory()->create();

        // When
        try {
            $this->workoutService->createWorkout($routine);
        } catch (WorkoutServiceException $workoutServiceException) {
            // Then
            $this->assertSame('Unable to create a workout for a routine with no exercises', $workoutServiceException->getMessage());

            return;
        }

        $this->fail();

    }

    #[Test]
    public function it_creates_a_workout_from_a_routine(): void
    {
        // Given
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $routine->exercises()->save($exercise);

        // When
        $workout = $this->workoutService->createWorkout($routine);

        // Then
        $this->assertTrue($workout->routine->is($routine));
    }

    #[Test]
    public function it_creates_a_workout_exercise_for_each_exercise_in_the_routine(): void
    {
        // Given
        $exerciseOne = Exercise::factory()->create();
        $exerciseTwo = Exercise::factory()->create();

        $routine = Routine::factory()->create();

        $routine->exercises()->sync([$exerciseOne->id, $exerciseTwo->id]);

        // When
        $workout = $this->workoutService->createWorkout($routine);

        // Then
        $this->assertCount(2, $workout->exercises);

    }
}
