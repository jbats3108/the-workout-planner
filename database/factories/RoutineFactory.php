<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\RoutineType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RoutineFactory extends Factory
{
    protected $model = Routine::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'routine_type_id' => RoutineType::factory(),
            'owner_id' => User::factory(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function withOwner(User $user): RoutineFactory
    {
        return $this->state(fn (array $attributes) => ['owner_id' => $user->id]);
    }
}
