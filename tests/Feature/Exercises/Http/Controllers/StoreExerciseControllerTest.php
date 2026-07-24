<?php

namespace Tests\Feature\Exercises\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreExerciseControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    private MuscleGroup $validMuscleGroup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();

        $this->validMuscleGroup = MuscleGroup::factory()->create();
    }

    #[Test]
    public function it_only_allows_admins_to_create_exercises(): void
    {
        // Given
        $nonAdminUser = User::factory()->create()->assignRole('user');

        // When
        $response = $this->actingAs($nonAdminUser)->post('/exercises/create');

        // Then
        $response->assertForbidden();
    }

    #[Test]
    public function it_rejects_requests_with_invalid_muscle_groups(): void
    {
        // Given
        $createExerciseRequest = [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'primary_muscle_group' => 'invalid',
            'secondary_muscle_group' => null,
        ];

        // When
        $response = $this->makeRequest($createExerciseRequest);

        // Then
        $response->assertSessionHasErrors('primary_muscle_group');
    }

    #[Test]
    public function it_requires_the_secondary_muscle_group_to_be_different_to_the_primary(): void
    {
        // Given
        $createExerciseRequest = [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'primary_muscle_group' => $this->validMuscleGroup->getSlug(),
            'secondary_muscle_group' => $this->validMuscleGroup->getSlug(),
        ];

        // When
        $response = $this->makeRequest($createExerciseRequest);

        // Then
        $response->assertSessionHasErrors('secondary_muscle_group');
    }

    #[Test]
    public function it_creates_a_new_exercise_record(): void
    {
        // Given
        $createExerciseRequest = [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'primary_muscle_group' => $this->validMuscleGroup->getSlug(),
            'secondary_muscle_group' => null,
        ];

        // When
        $response = $this->makeRequest($createExerciseRequest);

        // Then
        $response->assertCreated();

        $this->assertDatabaseHas(Exercise::class, [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
        ]);

        $createdExercise = Exercise::lookup('test-exercise');

        $this->assertTrue($createdExercise->primaryMuscleGroup->is($this->validMuscleGroup));
        $this->assertNull($createdExercise->secondaryMuscleGroup);
    }

    /**
     * @param  array{
     *     name: string,
     *     slug: string,
     *     primary_muscle_group: string,
     *     secondary_muscle_group: string|null,
     * } $createExerciseRequest
     * @return TestResponse<Response>
     */
    private function makeRequest(array $createExerciseRequest): TestResponse
    {
        return $this->actingAs($this->adminUser)->post('/exercises/create', $createExerciseRequest);
    }
}
