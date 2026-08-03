<?php

namespace Tests\Feature\Workouts\Http\Controllers;

use App\Routines\Models\Routine;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\CreatesPlayableWorkout;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class StoreWorkoutControllerTest extends TestCase
{
    use CreatesPlayableWorkout;
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers(false);
    }

    #[Test]
    public function it_requires_the_routine_to_belong_to_the_user(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $response = $this->actingAs($this->secondUser)->post(route('workouts.store', $routine));

        $response->assertNotFound();
    }

    #[Test]
    public function it_requires_the_routine_to_have_at_least_one_exercise(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $response = $this->actingAs($this->user)->post(route('workouts.store', $routine));

        $response->assertRedirectBack();
        $response->assertRedirectBackWithErrors();
    }

    #[Test]
    public function it_creates_a_workout_and_redirects_to_the_player(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $this->seedPlayableRoutineBlock($routine, setCount: 3, restSeconds: null);

        $response = $this->actingAs($this->user)->post(route('workouts.store', $routine));

        $workout = Workout::query()->where('routine_id', $routine->id)->firstOrFail();

        $response->assertRedirect(route('workouts.play', $workout));
        $this->assertStringContainsString($workout->ulid, $response->headers->get('Location') ?? '');
        $this->assertSame(WorkoutStatus::InProgress, $workout->status);
    }

    #[Test]
    public function it_rejects_starting_a_second_in_progress_workout(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $this->seedPlayableRoutineBlock($routine, setCount: 3, restSeconds: null);
        $this->actingAs($this->user)->post(route('workouts.store', $routine));

        $other = Routine::factory()->withUser($this->user)->create();
        $this->seedPlayableRoutineBlock($other, setCount: 3, restSeconds: null);

        $response = $this->actingAs($this->user)->post(route('workouts.store', $other));

        $response->assertRedirectBack();
        $response->assertRedirectBackWithErrors();
        $this->assertSame(1, Workout::query()->where('user_id', $this->user->id)->count());
    }
}
