<?php

namespace Tests\Unit\Models;

use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutSetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_workout(): void
    {
        // Given
        $workout = Workout::factory()->create();

        $set = WorkoutSet::factory()->create([
            'workout_id' => $workout->id,
        ]);

        // When / Then
        $this->assertTrue($set->workout->is($workout));
    }

    #[Test]
    public function it_has_an_exercise(): void
    {
        // Given
        $exercise = Exercise::factory()->create();

        $set = WorkoutSet::factory()->create([
            'exercise_id' => $exercise->id,
        ]);

        // When /Then
        $this->assertTrue($set->exercise->is($exercise));
    }

    #[Test]
    public function it_allows_recording_the_weight(): void
    {
        // Given
        $set = WorkoutSet::factory()->create();

        // When
        $set->recordWeight(11.5);

        // Then
        $this->assertSame(11.5, $set->weight);

    }

    #[Test]
    public function it_allows_recording_the_reps(): void
    {
        // Given
        $set = WorkoutSet::factory()->create();

        // When
        $set->recordReps(12);

        // Then
        $this->assertSame(12, $set->reps);

    }

    #[Test]
    public function it_allows_recording_the_set_number(): void
    {
        // Given
        $workoutSet = WorkoutSet::factory()->create();

        // When
        $workoutSet->recordSetNumber(3);

        // Then
        $this->assertSame(3, $workoutSet->set);

    }
}
