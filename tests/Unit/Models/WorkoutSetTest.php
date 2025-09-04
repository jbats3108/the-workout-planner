<?php

namespace Tests\Unit\Models;

use App\Models\WorkoutExercise;
use App\Models\WorkoutSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutSetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_workout_exercise(): void
    {
        // Given
        $workoutExercise = WorkoutExercise::factory()->create();

        // When
        $workoutSet = WorkoutSet::factory()->create([
            'workout_exercise_id' => $workoutExercise->id,
        ]);

        // Then
        $this->assertTrue($workoutSet->workoutExercise->is($workoutExercise));
    }

    #[Test]
    public function it_allows_recording_reps(): void
    {
        // Given
        $workoutSet = WorkoutSet::factory()->create();

        // When
        $workoutSet->recordReps(6);

        // Then
        $this->assertSame(6, $workoutSet->reps);

    }

    #[Test]
    public function it_allows_recording_weight(): void
    {
        // Given
        $workoutSet = WorkoutSet::factory()->create();

        // When
        $workoutSet->recordWeight(92.5);

        // Then
        $this->assertSame(92.5, $workoutSet->weight);

    }
}
