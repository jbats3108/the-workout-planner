<?php

namespace Database\Seeders;

use App\Models\RoutineType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoutineTypeSeeder extends Seeder
{
    public function run(): void
    {
        $routineTypeNames = [
            'Cardio',
            'Chest',
            'Back',
            'Legs',
            'Full Body',
        ];

        foreach ($routineTypeNames as $routineTypeName) {
            $slug = Str::kebab($routineTypeName);

            RoutineType::create([
                'name' => $routineTypeName,
                'slug' => $slug,
            ]);
        }
    }
}
