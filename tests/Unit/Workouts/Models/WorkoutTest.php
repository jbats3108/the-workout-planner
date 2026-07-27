<?php

namespace Tests\Unit\Workouts\Models;

use App\Routines\Models\Routine;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_routine(): void
    {
        // Given
        $routine = Routine::factory()->create();

        $workout = Workout::factory()->create([
            'routine_id' => $routine->id,
        ]);

        // When
        $workoutRoutine = $workout->routine;

        // Then
        $this->assertTrue($workoutRoutine->is($routine));
    }

    #[Test]
    public function it_has_notes(): void
    {
        // Given
        $workout = Workout::factory()->create([
            'notes' => 'I am a note',
        ]);

        // When / Then
        $this->assertSame('I am a note', $workout->notes);
    }

    #[Test]
    public function it_has_blocks(): void
    {
        // Given
        $workout = Workout::factory()->create();

        WorkoutBlock::create([
            'workout_id' => $workout->id,
            'position' => 1,
        ]);

        // When / Then
        $this->assertCount(1, $workout->blocks);
    }
}
