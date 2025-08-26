<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutineExercise>
 */
class RoutineExerciseFactory extends Factory
{
    protected $model = RoutineExercise::class;

    public function definition(): array
    {
        return [
            'exercise_id' => Exercise::factory(),
            'routine_id' => Routine::factory(),
        ];
    }
}
