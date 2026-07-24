<?php

namespace App\Workouts\Services;

use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Shared\Enums\SetGroupType;
use App\Users\Models\User;
use App\Workouts\Enums\WorkoutMode;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutBlock;
use App\Workouts\Models\WorkoutBlockExercise;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Models\WorkoutSetGroup;
use App\Workouts\Models\WorkoutWarmUpStep;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\DataCollection;

class WorkoutService
{
    public const ROUTINE_HAS_NO_EXERCISES_ERROR = 'Unable to create a workout for a routine with no exercises';

    public const ALREADY_IN_PROGRESS_ERROR = 'You already have a workout in progress';

    public const WORKOUT_NOT_IN_PROGRESS_ERROR = 'This workout is not in progress';

    public const SET_ALREADY_COMPLETED_ERROR = 'Completed sets cannot be removed';

    public const CANNOT_REMOVE_LAST_WORKING_SET_ERROR = 'At least one working set is required';

    public const WORKING_SET_GROUP_MISSING_ERROR = 'This block has no working sets';

    public function __construct(
        private readonly WorkoutProgressionService $progressionService,
    ) {}

    /**
     * @throws WorkoutServiceException
     */
    public function createWorkout(Routine $routine, WorkoutMode $mode = WorkoutMode::Normal): Workout
    {
        $routine->load([
            'user',
            'blocks.blockExercises.exercise',
            'blocks.setGroups.warmUpSteps',
        ]);

        $hasExercises = $routine->blocks->contains(
            fn (RoutineBlock $block) => $block->blockExercises->isNotEmpty()
        );

        if (! $hasExercises) {
            throw new WorkoutServiceException(self::ROUTINE_HAS_NO_EXERCISES_ERROR);
        }

        if ($this->inProgressFor($routine->user) !== null) {
            throw new WorkoutServiceException(self::ALREADY_IN_PROGRESS_ERROR);
        }

        return DB::transaction(function () use ($routine, $mode): Workout {
            $workout = Workout::create([
                'user_id' => $routine->user_id,
                'routine_id' => $routine->id,
                'mode' => $mode,
                'status' => WorkoutStatus::InProgress,
                'started_at' => now(),
            ]);

            $weightFactor = $mode === WorkoutMode::Deload ? (float) $routine->deload_weight_factor : 1.0;
            $repsFactor = $mode === WorkoutMode::Deload ? (float) $routine->deload_reps_factor : 1.0;

            foreach ($routine->blocks as $routineBlock) {
                $workoutBlock = WorkoutBlock::create([
                    'workout_id' => $workout->id,
                    'position' => $routineBlock->position,
                    'is_superset' => $routineBlock->is_superset,
                    'has_setup_after' => $routineBlock->has_setup_after,
                ]);

                foreach ($routineBlock->blockExercises as $routineBlockExercise) {
                    WorkoutBlockExercise::create([
                        'workout_block_id' => $workoutBlock->id,
                        'exercise_id' => $routineBlockExercise->exercise_id,
                        'position' => $routineBlockExercise->position,
                        'exercise_name' => $routineBlockExercise->exercise->getName(),
                        'working_weight_g' => (int) round($routineBlockExercise->working_weight_g * $weightFactor),
                        'prescribed_reps' => max(1, (int) round($routineBlockExercise->prescribed_reps * $repsFactor)),
                        'achievement_floor' => $routineBlockExercise->achievement_floor_override
                            ?? $routine->user->achievement_floor_default,
                        'progression_target' => $routineBlockExercise->progression_target_override
                            ?? $routine->user->progression_target_default,
                    ]);
                }

                $workoutBlock->load('blockExercises');

                foreach ($routineBlock->setGroups as $routineSetGroup) {
                    $workoutSetGroup = WorkoutSetGroup::create([
                        'workout_block_id' => $workoutBlock->id,
                        'type' => $routineSetGroup->type,
                        'set_count' => $routineSetGroup->set_count,
                        'rest_seconds' => $routineSetGroup->rest_seconds,
                    ]);

                    foreach ($routineSetGroup->warmUpSteps as $warmUpStep) {
                        WorkoutWarmUpStep::create([
                            'workout_set_group_id' => $workoutSetGroup->id,
                            'position' => $warmUpStep->position,
                            'percent_of_working' => $warmUpStep->percent_of_working,
                            'reps' => $warmUpStep->reps,
                        ]);
                    }

                    for ($setIndex = 0; $setIndex < $routineSetGroup->set_count; $setIndex++) {
                        foreach ($workoutBlock->blockExercises as $workoutBlockExercise) {
                            WorkoutSet::create([
                                'workout_set_group_id' => $workoutSetGroup->id,
                                'workout_block_exercise_id' => $workoutBlockExercise->id,
                                'set_index' => $setIndex,
                            ]);
                        }
                    }
                }
            }

            return $workout->fresh(['blocks']);
        });
    }

    public function inProgressFor(User $user): ?Workout
    {
        return Workout::query()
            ->where('user_id', $user->id)
            ->where('status', WorkoutStatus::InProgress)
            ->latest('started_at')
            ->first();
    }

    /**
     * @throws WorkoutServiceException
     */
    public function completeSet(WorkoutSet $set, int $reps, int $weightGrams): WorkoutSet
    {
        $set->loadMissing('setGroup.block.workout');
        $workout = $set->setGroup->block->workout;

        if ($workout->status !== WorkoutStatus::InProgress) {
            throw new WorkoutServiceException(self::WORKOUT_NOT_IN_PROGRESS_ERROR);
        }

        $set->reps = $reps;
        $set->weight_g = $weightGrams;
        $set->completed_at = now();
        $set->save();

        return $set->fresh();
    }

    /**
     * @throws WorkoutServiceException
     */
    public function addWorkingSet(WorkoutBlock $block): WorkoutBlock
    {
        $block->loadMissing(['workout', 'blockExercises', 'workingSetGroup.sets']);

        if ($block->workout->status !== WorkoutStatus::InProgress) {
            throw new WorkoutServiceException(self::WORKOUT_NOT_IN_PROGRESS_ERROR);
        }

        $workingGroup = $block->workingSetGroup;

        if ($workingGroup === null) {
            throw new WorkoutServiceException(self::WORKING_SET_GROUP_MISSING_ERROR);
        }

        return DB::transaction(function () use ($block, $workingGroup): WorkoutBlock {
            $nextIndex = (int) $workingGroup->sets->max('set_index') + 1;

            foreach ($block->blockExercises as $exercise) {
                WorkoutSet::create([
                    'workout_set_group_id' => $workingGroup->id,
                    'workout_block_exercise_id' => $exercise->id,
                    'set_index' => $nextIndex,
                ]);
            }

            $workingGroup->set_count = $workingGroup->set_count + 1;
            $workingGroup->save();

            return $block->fresh(['blockExercises', 'setGroups.sets']);
        });
    }

    /**
     * @throws WorkoutServiceException
     */
    public function removeWorkingSetRound(WorkoutSet $set): void
    {
        $set->loadMissing(['setGroup.block.workout', 'setGroup.sets']);

        $group = $set->setGroup;
        $workout = $group->block->workout;

        if ($workout->status !== WorkoutStatus::InProgress) {
            throw new WorkoutServiceException(self::WORKOUT_NOT_IN_PROGRESS_ERROR);
        }

        if ($group->type !== SetGroupType::Working) {
            throw new WorkoutServiceException(self::WORKING_SET_GROUP_MISSING_ERROR);
        }

        $roundSets = $group->sets->where('set_index', $set->set_index);

        if ($roundSets->contains(fn (WorkoutSet $roundSet): bool => $roundSet->completed_at !== null)) {
            throw new WorkoutServiceException(self::SET_ALREADY_COMPLETED_ERROR);
        }

        if ($group->set_count <= 1) {
            throw new WorkoutServiceException(self::CANNOT_REMOVE_LAST_WORKING_SET_ERROR);
        }

        DB::transaction(function () use ($group, $roundSets): void {
            foreach ($roundSets as $roundSet) {
                $roundSet->delete();
            }

            $group->set_count = max(1, $group->set_count - 1);
            $group->save();
        });
    }

    /**
     * @return DataCollection<int, \App\Workouts\Data\Progression\BumpProposalData>
     *
     * @throws WorkoutServiceException
     */
    public function finishWorkout(Workout $workout): DataCollection
    {
        if ($workout->status !== WorkoutStatus::InProgress) {
            throw new WorkoutServiceException(self::WORKOUT_NOT_IN_PROGRESS_ERROR);
        }

        return DB::transaction(function () use ($workout): DataCollection {
            $workout->status = WorkoutStatus::Finished;
            $workout->finished_at = now();
            $workout->save();

            return $this->progressionService->applyCarryForwardAndCollectBumps($workout);
        });
    }

    /**
     * @throws WorkoutServiceException
     */
    public function discardWorkout(Workout $workout): void
    {
        if ($workout->status !== WorkoutStatus::InProgress) {
            throw new WorkoutServiceException(self::WORKOUT_NOT_IN_PROGRESS_ERROR);
        }

        $workout->status = WorkoutStatus::Discarded;
        $workout->save();
    }
}
