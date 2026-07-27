<?php

namespace Tests\Feature\Workouts\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
use App\Shared\Enums\SetGroupType;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class WorkingSetControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function owner_can_add_a_working_set_on_in_progress_workout(): void
    {
        $workout = $this->createWorkoutWithSetCount(2);
        $block = $workout->blocks->first();

        $this->actingAs($this->user)
            ->from(route('workouts.play', $workout))
            ->post(route('workouts.working-sets.add', ['workout' => $workout, 'block' => $block]))
            ->assertRedirect(route('workouts.play', $workout));
    }

    #[Test]
    public function owner_can_remove_a_working_set_on_in_progress_workout(): void
    {
        $workout = $this->createWorkoutWithSetCount(2);
        $set = WorkoutSet::query()
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $workout->id))
            ->orderByDesc('set_index')
            ->firstOrFail();

        $this->actingAs($this->user)
            ->from(route('workouts.play', $workout))
            ->delete(route('workouts.sets.remove', ['workout' => $workout, 'set' => $set]))
            ->assertRedirect(route('workouts.play', $workout));
    }

    #[Test]
    public function non_owner_cannot_add_working_sets(): void
    {
        $workout = $this->createWorkoutWithSetCount(2);
        $block = $workout->blocks->first();

        $this->actingAs($this->secondUser)
            ->post(route('workouts.working-sets.add', ['workout' => $workout, 'block' => $block]))
            ->assertForbidden();
    }

    #[Test]
    public function finished_workout_cannot_add_working_sets(): void
    {
        $workout = $this->createWorkoutWithSetCount(2);
        $workout->update(['status' => WorkoutStatus::Finished]);
        $block = $workout->blocks->first();

        $this->actingAs($this->user)
            ->post(route('workouts.working-sets.add', ['workout' => $workout, 'block' => $block]))
            ->assertForbidden();
    }

    #[Test]
    public function add_rejects_block_from_another_workout(): void
    {
        $workout = $this->createWorkoutWithSetCount(2);
        $other = $this->createWorkoutWithSetCount(2, forSecondUser: true);
        $foreignBlock = $other->blocks->first();

        $this->actingAs($this->user)
            ->post(route('workouts.working-sets.add', ['workout' => $workout, 'block' => $foreignBlock]))
            ->assertNotFound();
    }

    #[Test]
    public function remove_rejects_set_from_another_workout(): void
    {
        $workout = $this->createWorkoutWithSetCount(2);
        $other = $this->createWorkoutWithSetCount(2, forSecondUser: true);
        $foreignSet = WorkoutSet::query()
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $other->id))
            ->firstOrFail();

        $this->actingAs($this->user)
            ->delete(route('workouts.sets.remove', ['workout' => $workout, 'set' => $foreignSet]))
            ->assertNotFound();
    }

    private function createWorkoutWithSetCount(int $setCount, bool $forSecondUser = false): Workout
    {
        $user = $forSecondUser ? $this->secondUser : $this->user;
        $routine = Routine::factory()->withUser($user)->create();
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
            'set_count' => $setCount,
            'rest_seconds' => 90,
        ]);

        return app(WorkoutService::class)->createWorkout($routine)->load('blocks');
    }
}
