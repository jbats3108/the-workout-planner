<?php

namespace Tests\Feature\Exercises\Http\Controllers;

use App\Exercises\Data\ExerciseData;
use App\Exercises\Models\Exercise;
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
    #[DataProvider('provideUserRoles')]
    public function it_returns_shared_exercises_for_the_user(string $userRole): void
    {
        $shared = Exercise::factory()->count(2)->create();
        $otherCustom = Exercise::factory()->create(['user_id' => $this->secondUser->id]);

        $user = $this->createUser($userRole);

        $response = $this->actingAs($user)->get(route('exercises.index'));

        $response->assertOk();
        $shared->each(fn (Exercise $exercise) => $this->assertContains(
            ExerciseData::fromExercise($exercise)->toArray(),
            $response->json()
        ));
        $this->assertNotContains(
            ExerciseData::fromExercise($otherCustom)->toArray(),
            $response->json()
        );
    }

    #[Test]
    public function it_includes_the_authenticated_users_custom_exercises(): void
    {
        $custom = Exercise::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('exercises.index'));

        $response->assertOk();
        $this->assertContains(
            ExerciseData::fromExercise($custom)->toArray(),
            $response->json()
        );
    }
}
