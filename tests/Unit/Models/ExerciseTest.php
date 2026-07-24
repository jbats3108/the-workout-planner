<?php

namespace Tests\Unit\Models;

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
    public function it_can_be_queried_by_muscle_group(): void
    {
        // Given
        $chestGroup = MuscleGroup::factory()->create(['name' => 'Chest']);
        $backGroup = MuscleGroup::factory()->create(['name' => 'Back']);

        $chestExerciseOne = Exercise::factory()->create([
            'primary_muscle_group_id' => $chestGroup->id, 'name' => 'Bench Press',
        ]);
        $chestExerciseTwo = Exercise::factory()->create([
            'primary_muscle_group_id' => $chestGroup->id, 'name' => 'Push Up',
        ]);

        $backExercise = Exercise::factory()->create(['primary_muscle_group_id' => $backGroup->id, 'name' => 'Pull Up']);
        $backExerciseTwo = Exercise::factory()->create([
            'primary_muscle_group_id' => $backGroup->id, 'name' => 'Barbell Row',
        ]);

        // When
        $chestExercises = Exercise::whereMuscleGroup($chestGroup)->get();
        $backExercises = Exercise::whereMuscleGroup($backGroup)->get();

        // Then
        $this->assertCount(2, $chestExercises);
        $this->assertCount(2, $backExercises);

        $this->assertTrue($chestExercises->contains($chestExerciseOne));
        $this->assertTrue($chestExercises->contains($chestExerciseTwo));

        $this->assertTrue($backExercises->contains($backExercise));
        $this->assertTrue($backExercises->contains($backExerciseTwo));
    }

    #[Test]
    public function querying_by_muscle_group_also_searches_secondary_muscle_group(): void
    {
        // Given
        $chestGroup = MuscleGroup::factory()->create(['name' => 'Chest']);
        $tricepsGroup = MuscleGroup::factory()->create(['name' => 'Triceps']);

        $exercise = Exercise::factory()->create([
            'primary_muscle_group_id' => $chestGroup->id,
            'secondary_muscle_group_id' => $tricepsGroup->id,
            'name' => 'Tricep Dip',
        ]);

        // When
        $chestExercises = Exercise::whereMuscleGroup($tricepsGroup)->get();

        // Then
        $this->assertCount(1, $chestExercises);
        $this->assertTrue($chestExercises->contains($exercise));
    }
}
