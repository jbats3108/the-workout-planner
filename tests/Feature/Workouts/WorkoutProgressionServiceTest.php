<?php

namespace Tests\Feature\Workouts;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineDropsetSegment;
use App\Routines\Models\RoutineSetGroup;
use App\Shared\Enums\SetGroupType;
use App\Users\Models\User;
use App\Workouts\Enums\WorkoutMode;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Services\WorkoutProgressionService;
use App\Workouts\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutProgressionServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkoutService $workoutService;

    private WorkoutProgressionService $progressionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workoutService = app(WorkoutService::class);
        $this->progressionService = app(WorkoutProgressionService::class);
    }

    #[Test]
    public function finish_carries_forward_highest_achieved_weight(): void
    {
        [$routine, $routineExercise] = $this->seedRoutine(workingWeightG: 80000, progressionTarget: 6, achievementFloor: 4);
        $workout = $this->workoutService->createWorkout($routine);
        $set = $this->firstSet($workout->id);

        $this->workoutService->completeSet($set, reps: 5, weightGrams: 85000);
        $this->workoutService->finishWorkout($workout);

        $this->assertSame(85000, $routineExercise->fresh()->working_weight_g);
    }

    #[Test]
    public function finish_offers_bump_when_progression_target_is_hit(): void
    {
        [$routine, $routineExercise] = $this->seedRoutine(workingWeightG: 80000, progressionTarget: 6, achievementFloor: 4);
        $workout = $this->workoutService->createWorkout($routine);
        $set = $this->firstSet($workout->id);

        $this->workoutService->completeSet($set, reps: 6, weightGrams: 80000);
        $bumps = $this->workoutService->finishWorkout($workout);

        $this->assertCount(1, $bumps);
        $this->assertSame($routineExercise->id, $bumps->first()->routineBlockExerciseId);
        $this->assertSame(80000, $bumps->first()->fromWeightG);
        $this->assertSame(82500, $bumps->first()->toWeightG);
    }

    #[Test]
    public function deload_finish_skips_carry_forward_and_bumps(): void
    {
        [$routine, $routineExercise] = $this->seedRoutine(workingWeightG: 80000, progressionTarget: 3, achievementFloor: 1);
        $routine->update(['deload_weight_factor' => 0.5, 'deload_reps_factor' => 1]);
        $workout = $this->workoutService->createWorkout($routine, WorkoutMode::Deload);
        $set = $this->firstSet($workout->id);

        $this->workoutService->completeSet($set, reps: 6, weightGrams: 50000);
        $bumps = $this->workoutService->finishWorkout($workout);

        $this->assertCount(0, $bumps);
        $this->assertSame(80000, $routineExercise->fresh()->working_weight_g);
    }

    #[Test]
    public function confirmed_bumps_update_routine_working_weights(): void
    {
        [$routine, $routineExercise] = $this->seedRoutine(workingWeightG: 80000, progressionTarget: 6, achievementFloor: 4);
        $workout = $this->workoutService->createWorkout($routine);
        $set = $this->firstSet($workout->id);
        $this->workoutService->completeSet($set, reps: 6, weightGrams: 80000);
        $bumps = $this->workoutService->finishWorkout($workout);

        $this->progressionService->applyConfirmedBumps($bumps, [$routineExercise->id]);

        $this->assertSame(82500, $routineExercise->fresh()->working_weight_g);
    }

    #[Test]
    public function finish_ignores_dropsets_for_carry_forward_and_bumps(): void
    {
        [$routine, $routineExercise] = $this->seedRoutine(workingWeightG: 80000, progressionTarget: 6, achievementFloor: 4);
        $working = $routine->blocks->first()->setGroups->firstWhere('type', SetGroupType::Working);
        RoutineDropsetSegment::create([
            'routine_set_group_id' => $working->id,
            'set_index' => 0,
            'position' => 1,
            'weight_g' => 90000,
        ]);
        RoutineDropsetSegment::create([
            'routine_set_group_id' => $working->id,
            'set_index' => 0,
            'position' => 2,
            'weight_g' => 85000,
        ]);

        $workout = $this->workoutService->createWorkout($routine);
        $set = $this->firstSet($workout->id);
        $this->workoutService->completeSet($set, reps: 10, weightGrams: null, segmentWeightGrams: [90000, 85000]);
        $bumps = $this->workoutService->finishWorkout($workout);

        $this->assertCount(0, $bumps);
        $this->assertSame(80000, $routineExercise->fresh()->working_weight_g);
    }

    /**
     * @return array{0: Routine, 1: RoutineBlockExercise}
     */
    private function seedRoutine(int $workingWeightG, int $progressionTarget, int $achievementFloor): array
    {
        $user = User::factory()->create([
            'progression_target_default' => $progressionTarget,
            'achievement_floor_default' => $achievementFloor,
        ]);
        $routine = Routine::factory()->create(['user_id' => $user->id]);
        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);
        $routineExercise = RoutineBlockExercise::create([
            'routine_block_id' => $block->id,
            'exercise_id' => Exercise::factory()->create()->id,
            'position' => 1,
            'working_weight_g' => $workingWeightG,
            'prescribed_reps' => 6,
        ]);
        RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::Working,
            'set_count' => 1,
        ]);

        return [$routine->fresh(['user', 'blocks.blockExercises', 'blocks.setGroups']), $routineExercise];
    }

    private function firstSet(int $workoutId): WorkoutSet
    {
        return WorkoutSet::query()
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $workoutId))
            ->firstOrFail();
    }
}
