<?php

namespace App\Exercises\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ConvertFreeExerciseDbCommand extends Command
{
    protected $signature = 'exercises:convert-free-exercise-db
                            {source? : Path to free-exercise-db dist/exercises.json (downloads if omitted)}
                            {--output= : Output catalog path (default: database/data/exercises.json)}
                            {--all : Include stretching, plyometrics, and cardio}';

    protected $description = 'Convert yuhonas/free-exercise-db JSON into an OVRLOAD exercises:import catalog';

    private const SOURCE_URL = 'https://raw.githubusercontent.com/yuhonas/free-exercise-db/main/dist/exercises.json';

    /** @var array<string, string> free-exercise-db muscle → OVRLOAD slug */
    private const MUSCLE_MAP = [
        'abdominals' => 'core',
        'abductors' => 'abductors',
        'adductors' => 'adductors',
        'biceps' => 'biceps',
        'calves' => 'calves',
        'chest' => 'chest',
        'forearms' => 'forearms',
        'glutes' => 'glutes',
        'hamstrings' => 'hamstrings',
        'lats' => 'back',
        'lower back' => 'back',
        'middle back' => 'back',
        'neck' => 'neck',
        'quadriceps' => 'quads',
        'shoulders' => 'shoulders',
        'traps' => 'traps',
        'triceps' => 'triceps',
    ];

    /** @var array<string, string> slug → display name */
    private const GROUP_NAMES = [
        'back' => 'Back',
        'chest' => 'Chest',
        'shoulders' => 'Shoulders',
        'quads' => 'Quads',
        'hamstrings' => 'Hamstrings',
        'glutes' => 'Glutes',
        'calves' => 'Calves',
        'biceps' => 'Biceps',
        'triceps' => 'Triceps',
        'core' => 'Core',
        'traps' => 'Traps',
        'forearms' => 'Forearms',
        'abductors' => 'Abductors',
        'adductors' => 'Adductors',
        'neck' => 'Neck',
    ];

    private const DEFAULT_CATEGORIES = [
        'strength',
        'powerlifting',
        'olympic weightlifting',
        'strongman',
    ];

    public function handle(): int
    {
        $sourcePath = $this->argument('source');
        $tmpDownloaded = null;

        if ($sourcePath === null) {
            $this->info('Downloading '.self::SOURCE_URL);
            $json = @file_get_contents(self::SOURCE_URL);
            if ($json === false) {
                $this->error('Failed to download free-exercise-db.');

                return self::FAILURE;
            }
            $tmpDownloaded = tempnam(sys_get_temp_dir(), 'fedb-');
            file_put_contents($tmpDownloaded, $json);
            $sourcePath = $tmpDownloaded;
        }

        if (! is_readable($sourcePath)) {
            $this->error("Source not readable: {$sourcePath}");

            return self::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($sourcePath), true);
        if (! is_array($rows)) {
            $this->error('Source is not valid JSON.');

            return self::FAILURE;
        }

        $categories = $this->option('all')
            ? null
            : array_fill_keys(self::DEFAULT_CATEGORIES, true);

        $usedSlugs = [];
        $exercises = [];
        $skipped = 0;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                $skipped++;

                continue;
            }

            $category = strtolower((string) ($row['category'] ?? ''));
            if ($categories !== null && ! isset($categories[$category])) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $primaries = $row['primaryMuscles'] ?? [];
            if ($name === '' || ! is_array($primaries) || $primaries === []) {
                $skipped++;

                continue;
            }

            $primarySlug = self::MUSCLE_MAP[strtolower((string) $primaries[0])] ?? null;
            if ($primarySlug === null) {
                $skipped++;

                continue;
            }

            $secondarySlug = null;
            $secondaries = $row['secondaryMuscles'] ?? [];
            if (is_array($secondaries)) {
                foreach ($secondaries as $secondary) {
                    $mapped = self::MUSCLE_MAP[strtolower((string) $secondary)] ?? null;
                    if ($mapped !== null && $mapped !== $primarySlug) {
                        $secondarySlug = $mapped;
                        break;
                    }
                }
            }

            $slug = $this->uniqueSlug($name, $usedSlugs);
            $usedSlugs[$slug] = true;

            $exercises[] = [
                'name' => $name,
                'slug' => $slug,
                'primary' => $primarySlug,
                'secondary' => $secondarySlug,
            ];
        }

        usort($exercises, fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

        $usedGroups = [];
        foreach ($exercises as $exercise) {
            $usedGroups[$exercise['primary']] = true;
            if (is_string($exercise['secondary'])) {
                $usedGroups[$exercise['secondary']] = true;
            }
        }

        $muscleGroups = [];
        foreach (self::GROUP_NAMES as $slug => $name) {
            if (isset($usedGroups[$slug])) {
                $muscleGroups[] = ['name' => $name, 'slug' => $slug];
            }
        }

        $catalog = [
            'muscle_groups' => $muscleGroups,
            'exercises' => $exercises,
        ];

        $output = $this->option('output') ?: database_path('data/exercises.json');
        $dir = dirname($output);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error("Could not create output directory: {$dir}");

            return self::FAILURE;
        }

        file_put_contents(
            $output,
            json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n"
        );

        if ($tmpDownloaded !== null) {
            @unlink($tmpDownloaded);
        }

        $this->info(sprintf(
            'Wrote %d exercises (%d muscle groups) to %s (skipped %d rows)',
            count($exercises),
            count($muscleGroups),
            $output,
            $skipped,
        ));
        $this->comment('Import with: php artisan exercises:import '.escapeshellarg($output));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, true>  $usedSlugs
     */
    private function uniqueSlug(string $name, array $usedSlugs): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'exercise';
        }

        $slug = $base;
        $i = 2;
        while (isset($usedSlugs[$slug])) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
