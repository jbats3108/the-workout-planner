<?php

namespace App\Workouts\Services;

use App\Workouts\Enums\WorkoutMode;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutBlock;
use App\Workouts\Models\WorkoutBlockExercise;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Models\WorkoutSetGroup;
use App\Workouts\Models\WorkoutWarmUpStep;

class WorkoutService
{
    public const ROUTINE_HAS_NO_EXERCISES_ERROR = 'Unable to create a workout for a routine with no exercises';

    /**
     * @throws WorkoutServiceException
     */
    public function createWorkout(Routine $routine): Workout
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

        $workout = Workout::create([
            'user_id' => $routine->user_id,
            'routine_id' => $routine->id,
            'mode' => WorkoutMode::Normal,
            'status' => WorkoutStatus::InProgress,
            'started_at' => now(),
        ]);

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
                    'working_weight_g' => $routineBlockExercise->working_weight_g,
                    'prescribed_reps' => $routineBlockExercise->prescribed_reps,
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

        return $workout;
    }
}
