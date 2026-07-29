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
use App\Workouts\Services\WorkoutProgressionService;
use App\Workouts\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class ProgressionControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function finish_with_bumps_redirects_to_progression_and_stores_session(): void
    {
        [$workout, $routineExercise] = $this->createFinishedEligibleWorkout();

        $this->actingAs($this->user)
            ->post(route('workouts.finish', $workout))
            ->assertRedirect(route('workouts.progression', $workout));

        $this->assertSame(WorkoutStatus::Finished, $workout->fresh()->status);
        $this->assertNotEmpty(session("workout_progression.{$workout->id}"));
        $this->assertSame(
            $routineExercise->id,
            session("workout_progression.{$workout->id}")[0]['routine_block_exercise_id']
                ?? session("workout_progression.{$workout->id}")[0]['routineBlockExerciseId']
                ?? null
        );
    }

    #[Test]
    public function show_renders_progression_when_session_present(): void
    {
        [$workout] = $this->createFinishedEligibleWorkout();
        $this->actingAs($this->user)->post(route('workouts.finish', $workout));

        $this->actingAs($this->user)
            ->get(route('workouts.progression', $workout))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('workouts/Progression')
                ->where('progression.workout_id', $workout->id)
                ->has('progression.bumps', 1));
    }

    #[Test]
    public function show_renders_undo_proposals_when_present(): void
    {
        [$workout, $routineExercise] = $this->createFinishedEligibleWorkout();
        $workoutService = app(WorkoutService::class);
        $progressionService = app(WorkoutProgressionService::class);
        $bumps = $workoutService->finishWorkout($workout);
        $progressionService->applyConfirmedBumps($workout, $bumps, [$routineExercise->id]);

        $set = WorkoutSet::query()
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $workout->id))
            ->firstOrFail();
        $set->update(['reps' => 4]);

        $reEval = $progressionService->reEvaluateProgression($workout->fresh());
        $progressionService->storeProgressionSession($workout, $reEval);

        $this->actingAs($this->user)
            ->get(route('workouts.progression', $workout))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('progression.undos', 1));
    }

    #[Test]
    public function show_redirects_to_dashboard_without_session(): void
    {
        [$workout] = $this->createFinishedEligibleWorkout();
        $this->actingAs($this->user)->post(route('workouts.finish', $workout));
        session()->forget("workout_progression.{$workout->id}");

        $this->actingAs($this->user)
            ->get(route('workouts.progression', $workout))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'No progression to review for that workout.');
    }

    #[Test]
    public function show_redirects_to_dashboard_when_not_finished(): void
    {
        $workout = $this->createInProgressWorkout();

        $this->actingAs($this->user)
            ->withSession(["workout_progression.{$workout->id}" => [
                [
                    'routine_block_exercise_id' => 1,
                    'from_weight_g' => 80000,
                    'to_weight_g' => 82500,
                    'exercise_name' => 'Squat',
                ],
            ]])
            ->get(route('workouts.progression', $workout))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Progression is only available for finished workouts.');
    }

    #[Test]
    public function apply_clears_session_and_redirects_to_dashboard(): void
    {
        [$workout, $routineExercise] = $this->createFinishedEligibleWorkout();
        $this->actingAs($this->user)->post(route('workouts.finish', $workout));

        $this->actingAs($this->user)
            ->post(route('workouts.progression.apply', $workout), [
                'routine_block_exercise_ids' => [$routineExercise->id],
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertNull(session("workout_progression.{$workout->id}"));
    }

    #[Test]
    public function skip_clears_session_and_redirects_to_dashboard(): void
    {
        [$workout] = $this->createFinishedEligibleWorkout();
        $this->actingAs($this->user)->post(route('workouts.finish', $workout));

        $this->actingAs($this->user)
            ->post(route('workouts.progression.skip', $workout))
            ->assertRedirect(route('dashboard'));

        $this->assertNull(session("workout_progression.{$workout->id}"));
    }

    #[Test]
    public function non_owner_cannot_apply_progression(): void
    {
        [$workout, $routineExercise] = $this->createFinishedEligibleWorkout();
        $this->actingAs($this->user)->post(route('workouts.finish', $workout));

        $this->actingAs($this->secondUser)
            ->post(route('workouts.progression.apply', $workout), [
                'routine_block_exercise_ids' => [$routineExercise->id],
            ])
            ->assertNotFound();
    }

    #[Test]
    public function in_progress_workout_cannot_apply_progression(): void
    {
        $workout = $this->createInProgressWorkout();

        $this->actingAs($this->user)
            ->post(route('workouts.progression.apply', $workout), [
                'routine_block_exercise_ids' => [1],
            ])
            ->assertForbidden();
    }

    /**
     * @return array{0: Workout, 1: RoutineBlockExercise}
     */
    private function createFinishedEligibleWorkout(): array
    {
        $this->user->update([
            'progression_target_default' => 6,
            'achievement_floor_default' => 4,
        ]);

        $routine = Routine::factory()->withUser($this->user)->create();
        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);
        $routineExercise = RoutineBlockExercise::create([
            'routine_block_id' => $block->id,
            'exercise_id' => Exercise::factory()->create()->id,
            'position' => 1,
            'working_weight_g' => 80000,
            'prescribed_reps' => 6,
            'progression_target_override' => 6,
            'achievement_floor_override' => 4,
        ]);
        RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::Working,
            'set_count' => 1,
            'rest_seconds' => 90,
        ]);

        $workout = app(WorkoutService::class)->createWorkout($routine);
        $set = WorkoutSet::query()
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $workout->id))
            ->firstOrFail();

        app(WorkoutService::class)->completeSet($set, reps: 6, weightGrams: 80000);

        return [$workout->fresh(), $routineExercise];
    }

    private function createInProgressWorkout(): Workout
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
