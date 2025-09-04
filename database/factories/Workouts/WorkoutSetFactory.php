<?php

namespace Database\Factories\Workouts;

use App\Models\Workouts\WorkoutExercise;
use App\Models\Workouts\WorkoutSet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<WorkoutSet> */
class WorkoutSetFactory extends Factory
{
    protected $model = WorkoutSet::class;

    public function definition(): array
    {
        return [
            'set' => $this->faker->randomNumber(),
            'reps' => $this->faker->randomNumber(),
            'weight' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'workout_exercise_id' => WorkoutExercise::factory(),
        ];
    }
}
