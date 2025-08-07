<?php

namespace Tests\Unit\Models;

use App\Enums\Difficulty;
use App\Enums\MovementType;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\Workout;
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
        $this->assertSame(Difficulty::ADVANCED, $exercise->difficulty);

    }

    #[Test]
    public function it_has_an_array_of_required_equipment(): void
    {
        // Given
        $equipment = [
            'barbell',
            'bench'
        ];

        // When
        $exercise = Exercise::factory()->create([
                'equipment' => $equipment
            ]
        );

        // Then
        $this->assertSame($equipment, $exercise->equipment);

    }

    #[Test]
    public function it_can_be_linked_to_multiple_workouts(): void
    {
        // Given
        $exercise = Exercise::factory()->create();

        $workoutOne = Workout::factory()->create();
        $workoutTwo = Workout::factory()->create();

        // When
        $workoutOne->exercises()->attach($exercise);
        $workoutTwo->exercises()->attach($exercise);

        // Then
        $this->assertCount(2, $exercise->workouts);

    }

    #[Test]
    public function it_can_be_queried_by_muscle_group(): void
    {
        // Given
        $chestGroup = MuscleGroup::factory()->create(['name' => 'Chest']);
        $backGroup = MuscleGroup::factory()->create(['name' => 'Back']);

        $chestExerciseOne = Exercise::factory()->create([
            'primary_muscle_group_id' => $chestGroup->id, 'name' => 'Bench Press'
        ]);
        $chestExerciseTwo = Exercise::factory()->create([
            'primary_muscle_group_id' => $chestGroup->id, 'name' => 'Push Up'
        ]);

        $backExercise = Exercise::factory()->create(['primary_muscle_group_id' => $backGroup->id, 'name' => 'Pull Up']);
        $backExerciseTwo = Exercise::factory()->create([
            'primary_muscle_group_id' => $backGroup->id, 'name' => 'Barbell Row'
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
            'primary_muscle_group_id'   => $chestGroup->id,
            'secondary_muscle_group_id' => $tricepsGroup->id,
            'name'                      => 'Tricep Dip'
        ]);

        // When
        $chestExercises = Exercise::whereMuscleGroup($tricepsGroup)->get();

        // Then
        $this->assertCount(1, $chestExercises);
        $this->assertTrue($chestExercises->contains($exercise));

    }

    #[Test]
    public function it_can_be_queried_by_movement_type(): void
    {
        // Given
        $pushExercise = Exercise::factory()->create([
            'movement_type' => MovementType::PUSH
        ]);

        $pullExercise = Exercise::factory()->create([
            'movement_type' => MovementType::PULL
        ]);

        // When
        $pushExercises = Exercise::whereMovementType(MovementType::PUSH)->get();
        $pullExercises = Exercise::whereMovementType(MovementType::PULL)->get();

        // Then
        $this->assertCount(1, $pushExercises);
        $this->assertTrue($pushExercises->contains($pushExercise));

        $this->assertCount(1, $pullExercises);
        $this->assertTrue($pullExercises->contains($pullExercise));

    }

    #[Test]
    public function it_can_be_queried_by_difficulty(): void
    {
        // Given
        $beginnerExercise = Exercise::factory()->create([
            'difficulty' => Difficulty::BEGINNER
        ]);

        $advancedExercise = Exercise::factory()->create([
            'difficulty' => Difficulty::ADVANCED
        ]);

        // When
        $beginnerExercises = Exercise::whereDifficulty(Difficulty::BEGINNER)->get();
        $advancedExercises = Exercise::whereDifficulty(Difficulty::ADVANCED)->get();

        // Then
        $this->assertCount(1, $beginnerExercises);
        $this->assertTrue($beginnerExercises->contains($beginnerExercise));

        $this->assertCount(1, $advancedExercises);
        $this->assertTrue($advancedExercises->contains($advancedExercise));
    }

}
