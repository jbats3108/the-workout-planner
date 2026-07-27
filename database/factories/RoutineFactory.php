<?php

namespace Database\Factories;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Routine>
 */
class RoutineFactory extends Factory
{
    protected $model = Routine::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'user_id' => User::factory(),
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function withUser(User $user): RoutineFactory
    {
        return $this->state(fn (array $attributes) => ['user_id' => $user->id]);
    }
}
