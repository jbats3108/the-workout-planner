<?php

namespace Database\Factories\Workouts;

use App\Enums\WorkoutMode;
use App\Enums\WorkoutStatus;
use App\Models\Routine;
use App\Models\User;
use App\Models\Workouts\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<Workout> */
class WorkoutFactory extends Factory
{
    protected $model = Workout::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'routine_id' => Routine::factory(),
            'mode' => WorkoutMode::Normal,
            'status' => WorkoutStatus::InProgress,
            'notes' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
