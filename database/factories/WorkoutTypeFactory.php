<?php

namespace Database\Factories;

use App\Models\WorkoutType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WorkoutTypeFactory extends Factory
{
    protected $model = WorkoutType::class;

    public function definition(): array
    {
        return [
            'name'       => $this->faker->name(),
            'slug'       => $this->faker->slug(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
