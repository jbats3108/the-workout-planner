<?php

namespace Tests\Unit\Models\Workouts;

use App\Models\Exercise;
use App\Models\Workouts\WorkoutBlock;
use App\Models\Workouts\WorkoutBlockExercise;
use App\Models\Workouts\WorkoutSet;
use App\Models\Workouts\WorkoutSetGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutSetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_block_exercise(): void
    {
        // Given
        $workoutBlock = WorkoutBlock::create([
            'workout_id' => \App\Models\Workouts\Workout::factory()->create()->id,
            'position' => 1,
        ]);

        $workoutBlockExercise = WorkoutBlockExercise::create([
            'workout_block_id' => $workoutBlock->id,
            'exercise_id' => Exercise::factory()->create()->id,
            'position' => 1,
            'exercise_name' => 'Test Exercise',
            'working_weight_g' => 80000,
            'prescribed_reps' => 6,
        ]);

        $workoutSetGroup = WorkoutSetGroup::create([
            'workout_block_id' => $workoutBlock->id,
            'type' => 'working',
            'set_count' => 3,
        ]);

        // When
        $workoutSet = WorkoutSet::create([
            'workout_set_group_id' => $workoutSetGroup->id,
            'workout_block_exercise_id' => $workoutBlockExercise->id,
            'set_index' => 0,
        ]);

        // Then
        $this->assertTrue($workoutSet->blockExercise->is($workoutBlockExercise));
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
        $workoutSet->recordWeight(92500);

        // Then
        $this->assertSame(92500, $workoutSet->weight_g);
    }
}
