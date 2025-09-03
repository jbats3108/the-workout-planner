<?php

namespace Tests\Unit\Models;

use App\Models\Routine;
use App\Models\Workout;
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

        // When
        $workoutNotes = $workout->getNotes();

        // Then
        $this->assertSame('I am a note', $workoutNotes);
    }

    #[Test]
    public function it_allows_adding_notes(): void
    {
        // Given
        $workout = Workout::factory()->create();

        $this->assertNull($workout->getNotes());

        $addedNote = 'I am a newly added note';

        // When
        $workout->addNotes($addedNote);

        // Then
        $this->assertSame($addedNote, $workout->getNotes());
    }
}
