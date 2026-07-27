<?php

namespace Tests\Unit\Exercises\Data;

use App\Exercises\Data\StoreExerciseData;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreExerciseDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_resolves_the_primary_muscle_group_slug_to_a_model(): void
    {
        // Given
        $muscleGroup = MuscleGroup::factory()->create();

        $createExerciseData = [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'primary_muscle_group' => $muscleGroup->getSlug(),
            'secondary_muscle_group' => null,
        ];

        // When
        $storeExerciseData = StoreExerciseData::from($createExerciseData);

        // Then
        $this->assertTrue($storeExerciseData->primaryMuscleGroup->is($muscleGroup));
    }

    #[Test]
    public function it_resolves_the_secondary_muscle_group_to_a_model(): void
    {
        $primaryMuscleGroup = MuscleGroup::factory()->create();
        $secondaryMuscleGroup = MuscleGroup::factory()->create();

        $createExerciseData = [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'primary_muscle_group' => $primaryMuscleGroup->getSlug(),
            'secondary_muscle_group' => $secondaryMuscleGroup->getSlug(),
        ];

        // When
        $storeExerciseData = StoreExerciseData::from($createExerciseData);

        // Then
        $this->assertTrue($storeExerciseData->secondaryMuscleGroup->is($secondaryMuscleGroup));
    }
}
