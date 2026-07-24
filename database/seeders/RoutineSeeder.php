<?php

namespace Database\Seeders;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use Illuminate\Database\Seeder;

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->limit(2)->get();

        $routineNames = [
            'Push Day',
            'Pull Day',
            'Chest Day',
            'Back Day',
            'Leg Day',
            'Upper Day',
            'Cardio Session',
        ];

        $users->each(function (User $user) use ($routineNames): void {
            $names = $routineNames;
            shuffle($names);

            foreach (array_slice($names, 0, 3) as $routineName) {
                Routine::create([
                    'name' => $routineName,
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
