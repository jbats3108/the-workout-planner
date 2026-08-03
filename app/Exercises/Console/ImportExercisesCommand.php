<?php

namespace App\Exercises\Console;

use App\Exercises\Services\ExerciseCatalogImporter;
use Illuminate\Console\Command;
use Override;
use Throwable;

class ImportExercisesCommand extends Command
{
    #[Override]
    protected $signature = 'exercises:import
                            {path? : Path to a catalog JSON file (defaults to database/data/exercises.json)}
                            {--no-prune : Keep shared exercises that are not in the catalog file}';

    #[Override]
    protected $description = 'Upsert shared exercises (and muscle groups) from a catalog JSON file; soft-delete shared lifts missing from the catalog unless --no-prune';

    public function handle(ExerciseCatalogImporter $importer): int
    {
        $path = $this->argument('path') ?: ExerciseCatalogImporter::defaultPath();
        $prune = ! $this->option('no-prune');

        try {
            $result = $importer->importFromPath($path, $prune);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Imported catalog from %s — muscle groups created: %d, exercises created: %d, updated: %d, skipped: %d, pruned: %d',
            $path,
            $result['muscle_groups'],
            $result['created'],
            $result['updated'],
            $result['skipped'],
            $result['pruned'],
        ));

        return self::SUCCESS;
    }
}
