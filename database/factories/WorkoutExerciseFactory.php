<?php

namespace Database\Factories;

use App\Models\RoutineExercise;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<WorkoutExercise> */
class WorkoutExerciseFactory extends Factory
{
    protected $model = WorkoutExercise::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'workout_id' => Workout::factory(),
            'routine_exercise_id' => RoutineExercise::factory(),
        ];
    }
}
