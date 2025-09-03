<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutSet;
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
            'weight' => $this->faker->randomFloat(),
            'reps' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'workout_id' => Workout::factory(),
            'exercise_id' => Exercise::factory(),
        ];
    }
}
