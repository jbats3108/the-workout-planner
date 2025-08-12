<?php

namespace Tests\Feature\Controllers\Exercises;

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

        $exercises->each(fn (Exercise $exercise) => $response->assertJsonFragment([
            'id' => $exercise->id,
            'name' => $exercise->name,
            'slug' => $exercise->slug,
            'primary_muscle_group_id' => $exercise->primary_muscle_group_id,
            'secondary_muscle_group_id' => $exercise->secondary_muscle_group_id,
            'equipment' => $exercise->equipment,
            'difficulty' => $exercise->difficulty,
            'movement_type' => $exercise->movement_type,
        ]));

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
