<?php

namespace Tests\Feature\Controllers\Exercises;

use App\DataTransferObjects\Exercises\ExerciseData;
use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class IndexExerciseControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    #[DataProvider('userRoles')]
    public function it_returns_all_exercises(string $userRole): void
    {
        // Given
        $exercises = Exercise::factory()->count(3)->create();

        // When
        $response = $this->actingAs($this->user)->get(route('exercises.index'));

        // Then
        $response->assertOk();

        $exercises->each(fn (Exercise $exercise) => $this->assertContains(
            ExerciseData::fromExercise($exercise)->toArray(),
            $response->json())
        );

    }

    public static function userRoles(): array
    {
        return
            [
                'Admin' => ['admin'],
                'User' => ['user'],
            ];
    }
}
