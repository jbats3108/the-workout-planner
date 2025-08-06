<?php

namespace Tests\Unit\Models;

use App\Models\Exercise;
use App\Models\User;
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

    #[Test]
    public function it_can_be_linked_to_multiple_exercises(): void
    {
        // Given
        $exerciseOne = Exercise::factory()->create();
        $exerciseTwo = Exercise::factory()->create();

        $workout = Workout::factory()->create();

        // When
        $exerciseOne->workouts()->attach($workout);
        $exerciseTwo->workouts()->attach($workout);

        // Then
        $this->assertCount(2, $workout->exercises);

    }

    #[Test]
    public function it_has_an_owner(): void
    {
        // Given
        $user = User::factory()->create();

        // When
        $workout = Workout::factory()->create([
            'owner_id' => $user->id,
        ]);

        // Then
        $this->assertTrue($workout->owner->is($user));;

    }

}
