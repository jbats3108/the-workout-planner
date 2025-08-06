<?php

namespace Tests\Unit\Models;

use App\Models\Workout;
use App\Models\WorkoutType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_type(): void
    {
    	// Given
        $workoutType = WorkoutType::factory()->create([
            'name' => 'Cardio',
        ]);

        // When
        $workout = Workout::factory()->create([
            'workout_type_id' => $workoutType->id,
        ]);

        // Then
        $this->assertTrue($workout->workoutType->is($workoutType));
        
    }
}
