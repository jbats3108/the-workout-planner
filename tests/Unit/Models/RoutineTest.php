<?php

namespace Tests\Unit\Models;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoutineTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_type(): void
    {
        // Given
        $routineType = RoutineType::factory()->create([
            'name' => 'Cardio',
        ]);

        // When
        $routine = Routine::factory()->create([
            'routine_type_id' => $routineType->id,
        ]);

        // Then
        $this->assertTrue($routine->routineType->is($routineType));
    }

    #[Test]
    public function it_can_be_linked_to_multiple_exercises(): void
    {
        // Given
        $exerciseOne = Exercise::factory()->create();
        $exerciseTwo = Exercise::factory()->create();

        $routine = Routine::factory()->create();

        // When
        $exerciseOne->routines()->attach($routine, [
            'sets' => 3,
            'reps' => 12,
            'weight' => 10,
        ]);
        $exerciseTwo->routines()->attach($routine, [
            'sets' => 3,
            'reps' => 12,
            'weight' => 10,
        ]);

        // Then
        $this->assertCount(2, $routine->exercises);

    }

    #[Test]
    public function it_has_an_owner(): void
    {
        // Given
        $user = User::factory()->create();

        // When
        $routine = Routine::factory()->create([
            'owner_id' => $user->id,
        ]);

        // Then
        $this->assertTrue($routine->owner->is($user));

    }
}
