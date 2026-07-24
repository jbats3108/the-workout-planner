<?php

namespace Database\Seeders;

use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $back = MuscleGroup::firstOrCreate(
            ['slug' => 'back'],
            ['name' => 'Back']
        );
        $chest = MuscleGroup::firstOrCreate(
            ['slug' => 'chest'],
            ['name' => 'Chest']
        );
        $legs = MuscleGroup::firstOrCreate(
            ['slug' => 'legs'],
            ['name' => 'Legs']
        );
        $shoulders = MuscleGroup::firstOrCreate(
            ['slug' => 'shoulders'],
            ['name' => 'Shoulders']
        );

        $catalog = [
            ['Barbell Deadlift', $back],
            ['Barbell Row', $back],
            ['Lat Pulldown', $back],
            ['Face Pull', $shoulders],
            ['Barbell Bench Press', $chest],
            ['Overhead Press', $shoulders],
            ['Back Squat', $legs],
            ['Romanian Deadlift', $legs],
        ];

        foreach ($catalog as [$name, $group]) {
            Exercise::firstOrCreate(
                ['slug' => Str::slug($name), 'user_id' => null],
                [
                    'name' => $name,
                    'primary_muscle_group_id' => $group->id,
                ]
            );
        }
    }
}
