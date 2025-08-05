<?php

namespace Database\Factories;

use App\Enums\MovementType;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'name'                      => $this->faker->name(),
            'slug'                      => $this->faker->slug(),
            'primary_muscle_group_id'   => MuscleGroup::factory(),
            'secondary_muscle_group_id' => null,
            'movement_type'             => MovementType::PULL,
            'created_at'                => Carbon::now(),
            'updated_at'                => Carbon::now(),
        ];
    }
}
