<?php

namespace Tests\Unit\Models;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineExercise;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutExerciseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_workout(): void
    {
        // Given
        $workout = Workout::factory()->create();

        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
        ]);

        // When / Then
        $this->assertTrue($workoutExercise->workout->is($workout));

    }

    #[Test]
    public function it_has_a_routine_exercise(): void
    {
        // Given
        $routineExercise = RoutineExercise::factory()->create();

        $workoutExercise = WorkoutExercise::factory()->create([
            'routine_exercise_id' => $routineExercise->id,
        ]);

        // When / Then
        $this->assertTrue($workoutExercise->routineExercise->is($routineExercise));

    }

    #[Test]
    public function it_has_an_exercise_through_the_routine_exercise(): void
    {
        // Given
        $exercise = Exercise::factory()->create();

        $routine = Routine::factory()->create();

        $routineExercise = RoutineExercise::factory()->create([
            'exercise_id' => $exercise->id,
            'routine_id' => $routine->id,
        ]);

        $workout = Workout::create([
            'routine_id' => $routine->id,
        ]);

        // When
        $workoutExercise = WorkoutExercise::create([
            'workout_id' => $workout->id,
            'routine_exercise_id' => $routineExercise->id,
        ]);

        // Then
        $this->assertTrue($workoutExercise->exercise()->is($exercise));
    }
}
