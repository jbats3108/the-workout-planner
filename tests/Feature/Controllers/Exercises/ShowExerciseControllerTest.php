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

class ShowExerciseControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('userRoles')]
    public function it_returns_the_exercise(string $userRole): void
    {
        // Given
        $this->seed(RoleSeeder::class);

        $exercise = Exercise::factory()->create();

        $user = User::factory()->withRole($userRole)->create();

        // When
        $response = $this->actingAs($user)->get(route('exercises.show', $exercise));

        // Then
        $response->assertOk();

        $this->assertSame(ExerciseData::fromExercise($exercise)->toArray(), $response->json());

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
