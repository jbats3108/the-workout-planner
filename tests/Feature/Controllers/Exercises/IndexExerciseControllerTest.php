<?php

namespace Tests\Feature\Controllers\Exercises;

use App\DataTransferObjects\Exercises\ExerciseData;
use App\Models\Exercise;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexExerciseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    #[Test]
    #[DataProvider('userRoles')]
    public function it_returns_all_exercises(string $userRole): void
    {
        // Given
        $user = User::factory()->withRole($userRole)->create();

        $exercises = Exercise::factory()->count(3)->create();

        // When
        $response = $this->actingAs($user)->get(route('exercises.index'));

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
