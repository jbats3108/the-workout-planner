<?php

namespace Database\Factories\Workouts;

use App\Shared\Enums\SetGroupType;
use App\Exercises\Models\Exercise;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutBlock;
use App\Workouts\Models\WorkoutBlockExercise;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Models\WorkoutSetGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkoutSet> */
class WorkoutSetFactory extends Factory
{
    protected $model = WorkoutSet::class;

    public function definition(): array
    {
        $workout = Workout::factory()->create();

        $workoutBlock = WorkoutBlock::create([
            'workout_id' => $workout->id,
            'position' => 1,
            'is_superset' => false,
            'has_setup_after' => false,
        ]);

        $workoutSetGroup = WorkoutSetGroup::create([
            'workout_block_id' => $workoutBlock->id,
            'type' => SetGroupType::Working,
            'set_count' => 3,
            'rest_seconds' => 120,
        ]);

        $exercise = Exercise::factory()->create();

        $workoutBlockExercise = WorkoutBlockExercise::create([
            'workout_block_id' => $workoutBlock->id,
            'exercise_id' => $exercise->id,
            'position' => 1,
            'exercise_name' => $exercise->getName(),
            'working_weight_g' => 80000,
            'prescribed_reps' => 6,
        ]);

        return [
            'workout_set_group_id' => $workoutSetGroup->id,
            'workout_block_exercise_id' => $workoutBlockExercise->id,
            'set_index' => 0,
            'reps' => null,
            'weight_g' => null,
        ];
    }
}
