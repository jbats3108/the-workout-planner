<?php

namespace Database\Factories\Workouts;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use App\Workouts\Enums\WorkoutMode;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/** @extends Factory<Workout> */
class WorkoutFactory extends Factory
{
    protected $model = Workout::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'routine_id' => Routine::factory(),
            'ulid' => (string) Str::ulid(),
            'mode' => WorkoutMode::Standard,
            'status' => WorkoutStatus::InProgress,
            'notes' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
