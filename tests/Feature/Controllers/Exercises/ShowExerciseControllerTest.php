<?php

namespace Tests\Feature\Controllers\Exercises;

use App\DataTransferObjects\Exercises\ExerciseData;
use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class ShowExerciseControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    #[DataProvider('provideUserRoles')]
    public function it_returns_the_exercise(string $userRole): void
    {
        // Given
        $user = $this->createUser($userRole);

        $exercise = Exercise::factory()->create();

        // When
        $response = $this->actingAs($user)->get(route('exercises.show', $exercise));

        // Then
        $response->assertOk();

        $this->assertSame(ExerciseData::fromExercise($exercise)->toArray(), $response->json());

    }
}
