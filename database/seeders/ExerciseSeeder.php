<?php

namespace Database\Seeders;

use App\Exercises\Services\ExerciseCatalogImporter;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        (new ExerciseCatalogImporter)->importFromPath(ExerciseCatalogImporter::defaultPath());
    }
}
