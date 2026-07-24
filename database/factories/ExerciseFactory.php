<?php

namespace Database\Factories;

use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'primary_muscle_group_id' => MuscleGroup::factory(),
            'secondary_muscle_group_id' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
