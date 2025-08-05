<?php

namespace Tests\Unit\Models;

use App\Enums\Difficulty;
use App\Enums\MovementType;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_name_and_slug(): void
    {
        // Given / When
        $exercise = Exercise::factory()->create(
            [
                'name' => 'Back Barbell Squat',
                'slug' => 'back-barbell-squat',
            ]
        );

        // Then
        $this->assertSame('Back Barbell Squat', $exercise->getName());
        $this->assertSame('back-barbell-squat', $exercise->getSlug());
    }

    #[Test]
    public function it_has_a_primary_muscle_group(): void
    {
        // Given
        $group = MuscleGroup::factory()->create(
            [
                'name' => 'Chest',
            ]
        );

        // When
        $exercise = Exercise::factory()->create(
            [
                'primary_muscle_group_id' => $group->id,
            ]
        );

        // Then
        $this->assertTrue($exercise->primaryMuscleGroup->is($group));

    }

    #[Test]
    public function it_has_a_secondary_muscle_group(): void
    {
        // Given
        $group = MuscleGroup::factory()->create([
            'name' => 'Upper Chest',
        ]);

        // When
        $exercise = Exercise::factory()->create([
            'secondary_muscle_group_id' => $group->id,
        ]);

        // Then
        $this->assertTrue($exercise->secondaryMuscleGroup->is($group));

    }

    #[Test]
    public function it_doesnt_need_to_have_a_secondary_muscle_group(): void
    {
        // Given / When
        $exercise = Exercise::factory()->create();

        // Then
        $this->assertNull($exercise->secondaryMuscleGroup);

    }

    #[Test]
    public function it_has_a_movement_type(): void
    {
        // Given / When
        $exercise = Exercise::factory()->create([
            'movement_type' => MovementType::PULL,
        ]);

        // Then
        $this->assertSame(MovementType::PULL, $exercise->movementType());

    }

    #[Test]
    public function it_has_a_difficulty_rating(): void
    {
        // Given / When
        $exercise = Exercise::factory()->create([
            'difficulty' => Difficulty::ADVANCED
        ]);

        // Then
        $this->assertSame(Difficulty::ADVANCED, $exercise->difficulty());

    }

}
