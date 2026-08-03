<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
use App\Shared\Enums\SetGroupType;
use App\Users\Models\User;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Services\WorkoutService;

/**
 * Requires a `$user` property (e.g. via {@see UserHelper}).
 */
trait CreatesPlayableWorkout
{
    /**
     * @return array{0: RoutineSetGroup, 1: RoutineBlockExercise}
     */
    protected function seedPlayableRoutineBlock(
        Routine $routine,
        int $setCount = 1,
        ?int $restSeconds = 90,
        int $workingWeightG = 80000,
        int $prescribedReps = 6,
        ?int $progressionTarget = null,
        ?int $achievementFloor = null,
    ): array {
        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);

        $exerciseAttributes = [
            'routine_block_id' => $block->id,
            'exercise_id' => Exercise::factory()->create()->id,
            'position' => 1,
            'working_weight_g' => $workingWeightG,
            'prescribed_reps' => $prescribedReps,
        ];

        if ($progressionTarget !== null) {
            $exerciseAttributes['progression_target_override'] = $progressionTarget;
        }

        if ($achievementFloor !== null) {
            $exerciseAttributes['achievement_floor_override'] = $achievementFloor;
        }

        $routineExercise = RoutineBlockExercise::create($exerciseAttributes);

        $groupAttributes = [
            'routine_block_id' => $block->id,
            'type' => SetGroupType::Working,
            'set_count' => $setCount,
        ];

        if ($restSeconds !== null) {
            $groupAttributes['rest_seconds'] = $restSeconds;
        }

        $group = RoutineSetGroup::create($groupAttributes);

        return [$group, $routineExercise];
    }

    protected function createPlayableWorkout(
        ?User $user = null,
        int $setCount = 1,
        bool $loadBlocks = false,
        ?int $restSeconds = 90,
        int $workingWeightG = 80000,
        int $prescribedReps = 6,
        ?int $progressionTarget = null,
        ?int $achievementFloor = null,
    ): Workout {
        $user ??= $this->user;

        $routine = Routine::factory()->withUser($user)->create();
        $this->seedPlayableRoutineBlock(
            $routine,
            setCount: $setCount,
            restSeconds: $restSeconds,
            workingWeightG: $workingWeightG,
            prescribedReps: $prescribedReps,
            progressionTarget: $progressionTarget,
            achievementFloor: $achievementFloor,
        );

        $workout = app(WorkoutService::class)->createWorkout($routine);

        return $loadBlocks ? $workout->load('blocks') : $workout;
    }

    /**
     * @return array{0: Workout, 1: RoutineBlockExercise}
     */
    protected function createFinishedEligibleWorkout(
        ?User $user = null,
        int $reps = 6,
        int $weightGrams = 80000,
    ): array {
        $user ??= $this->user;

        $user->update([
            'progression_target_default' => 6,
            'achievement_floor_default' => 4,
        ]);

        $routine = Routine::factory()->withUser($user)->create();
        [, $routineExercise] = $this->seedPlayableRoutineBlock(
            $routine,
            setCount: 1,
            restSeconds: 90,
            workingWeightG: 80000,
            prescribedReps: 6,
            progressionTarget: 6,
            achievementFloor: 4,
        );

        $workout = app(WorkoutService::class)->createWorkout($routine);
        app(WorkoutService::class)->completeSet(
            $this->firstWorkingSet($workout->id),
            reps: $reps,
            weightGrams: $weightGrams,
        );

        return [$workout->fresh(), $routineExercise];
    }

    /**
     * @return array{0: Workout, 1: RoutineBlockExercise, 2: Routine}
     */
    protected function createFinishedWorkout(
        ?User $user = null,
        int $reps = 6,
        int $weightGrams = 80000,
    ): array {
        $user ??= $this->user;

        $user->update([
            'progression_target_default' => 6,
            'achievement_floor_default' => 4,
        ]);

        $routine = Routine::factory()->withUser($user)->create();
        [, $routineExercise] = $this->seedPlayableRoutineBlock(
            $routine,
            setCount: 1,
            restSeconds: 90,
            workingWeightG: 80000,
            prescribedReps: 6,
            progressionTarget: 6,
            achievementFloor: 4,
        );

        $workoutService = app(WorkoutService::class);
        $workout = $workoutService->createWorkout($routine);
        $workoutService->completeSet(
            $this->firstWorkingSet($workout->id),
            reps: $reps,
            weightGrams: $weightGrams,
        );
        $workoutService->finishWorkout($workout);

        return [$workout->fresh(), $routineExercise, $routine];
    }

    protected function firstWorkingSet(int $workoutId): WorkoutSet
    {
        return WorkoutSet::query()
            ->whereHas('setGroup', fn ($q) => $q->where('type', SetGroupType::Working))
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $workoutId))
            ->firstOrFail();
    }
}
