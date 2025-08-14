<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Models\RoutineType;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->get();

        $routineTypeIds = RoutineType::all()->pluck('id');

        $users->each(function (User $user) use ($routineTypeIds) {
            $routineNames = [
                'Push Day',
                'Pull Day',
                'Chest Day',
                'Back Day',
                'Leg Day',
                'Upper Day',
                'Cardio Session',
            ];

            $idsToUse = $routineTypeIds->random(3);

            $idsToUse->each(function (int $routineTypeId) use (&$routineNames, $user) {

                $index = array_rand($routineNames);

                $routineName = $routineNames[$index];

                unset($routineNames[$index]);

                Routine::create([
                    'name' => $routineName,
                    'owner_id' => $user->id,
                    'routine_type_id' => $routineTypeId,
                ]);

            });

        });
    }
}
