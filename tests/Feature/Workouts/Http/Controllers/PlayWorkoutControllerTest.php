<?php

namespace Tests\Feature\Workouts\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
use App\Shared\Enums\SetGroupType;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class PlayWorkoutControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_renders_the_player_for_the_owner(): void
    {
        $workout = $this->createWorkoutForUser();

        $this->actingAs($this->user)
            ->get(route('workouts.play', $workout))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('workouts/Play')
                ->where('workout.id', $workout->id)
                ->where('workout.routine_name', $workout->routine->getName())
                ->has('workout.blocks', 1)
                ->has('plate_profile.bars')
                ->has('plate_profile.plates')
            );
    }

    #[Test]
    public function it_forbids_other_users(): void
    {
        $workout = $this->createWorkoutForUser();

        $this->actingAs($this->secondUser)
            ->get(route('workouts.play', $workout))
            ->assertForbidden();
    }

    #[Test]
    public function it_completes_a_set_and_finishes_the_workout(): void
    {
        $workout = $this->createWorkoutForUser();
        $set = WorkoutSet::query()
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $workout->id))
            ->firstOrFail();

        $this->actingAs($this->user)
            ->post(route('workouts.sets.complete', ['workout' => $workout, 'set' => $set]), [
                'reps' => 6,
                'weight_kg' => 80,
            ])
            ->assertRedirect();

        $set->refresh();
        $this->assertSame(6, $set->reps);
        $this->assertSame(80000, $set->weight_g);
        $this->assertNotNull($set->completed_at);

        $this->actingAs($this->user)
            ->post(route('workouts.finish', $workout))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(WorkoutStatus::Finished, $workout->fresh()->status);
    }

    private function createWorkoutForUser()
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);
        RoutineBlockExercise::create([
            'routine_block_id' => $block->id,
            'exercise_id' => Exercise::factory()->create()->id,
            'position' => 1,
            'working_weight_g' => 80000,
            'prescribed_reps' => 6,
        ]);
        RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::Working,
            'set_count' => 1,
            'rest_seconds' => 90,
        ]);

        return app(WorkoutService::class)->createWorkout($routine);
    }
}
