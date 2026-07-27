<?php

namespace App\Exercises\Console;

use App\Exercises\Services\ExerciseCatalogImporter;
use Illuminate\Console\Command;

class ImportExercisesCommand extends Command
{
    protected $signature = 'exercises:import
                            {path? : Path to a catalog JSON file (defaults to database/data/exercises.json)}';

    protected $description = 'Upsert shared exercises (and muscle groups) from a catalog JSON file';

    public function handle(ExerciseCatalogImporter $importer): int
    {
        $path = $this->argument('path') ?: ExerciseCatalogImporter::defaultPath();

        try {
            $result = $importer->importFromPath($path);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Imported catalog from %s — muscle groups created: %d, exercises created: %d, updated: %d, skipped: %d',
            $path,
            $result['muscle_groups'],
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
