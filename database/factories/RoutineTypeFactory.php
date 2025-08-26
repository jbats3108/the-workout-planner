<?php

namespace Database\Factories;

use App\Models\RoutineType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<RoutineType>
 */
class RoutineTypeFactory extends Factory
{
    protected $model = RoutineType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
