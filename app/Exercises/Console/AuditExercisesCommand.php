<?php

namespace App\Exercises\Console;

use App\Exercises\Models\Exercise;
use App\Exercises\Services\ExerciseCatalogImporter;
use Illuminate\Console\Command;
use Override;

class AuditExercisesCommand extends Command
{
    #[Override]
    protected $signature = 'exercises:audit
                            {--catalog= : Catalog JSON path (defaults to database/data/exercises.json)}
                            {--json : Print machine-readable JSON instead of tables}';

    #[Override]
    protected $description = 'Audit shared exercises: missing equipment, orphaned vs catalog, catalog not yet imported';

    public function handle(): int
    {
        $catalogPath = $this->option('catalog') ?: ExerciseCatalogImporter::defaultPath();

        if (! is_readable($catalogPath)) {
            $this->error("Catalog not readable: {$catalogPath}");

            return self::FAILURE;
        }

        $decoded = json_decode((string) file_get_contents($catalogPath), true);
        if (! is_array($decoded)) {
            $this->error("Catalog is not valid JSON: {$catalogPath}");

            return self::FAILURE;
        }

        /** @var array<string, array{name: string, equipment: ?string}> $catalogBySlug */
        $catalogBySlug = [];
        foreach ($decoded['exercises'] ?? [] as $row) {
            $slug = $row['slug'] ?? null;
            if (! is_string($slug) || $slug === '') {
                continue;
            }
            $catalogBySlug[$slug] = [
                'name' => is_string($row['name'] ?? null) ? $row['name'] : $slug,
                'equipment' => is_string($row['equipment'] ?? null) ? $row['equipment'] : null,
            ];
        }

        $shared = Exercise::query()->shared()->orderBy('name')->get(['id', 'name', 'slug', 'equipment']);

        $missingEquipment = $shared
            ->filter(fn (Exercise $exercise): bool => $exercise->equipment === null)
            ->values();

        $orphans = $shared
            ->filter(fn (Exercise $exercise): bool => ! array_key_exists($exercise->slug, $catalogBySlug))
            ->values();

        $notImported = collect($catalogBySlug)
            ->reject(fn (array $row, string $slug): bool => $shared->contains(fn (Exercise $e): bool => $e->slug === $slug))
            ->map(fn (array $row, string $slug): array => [
                'slug' => $slug,
                'name' => $row['name'],
                'equipment' => $row['equipment'],
            ])
            ->values();

        $payload = [
            'catalog' => $catalogPath,
            'shared_count' => $shared->count(),
            'catalog_count' => count($catalogBySlug),
            'missing_equipment' => $missingEquipment->map(fn (Exercise $e): array => [
                'id' => $e->id,
                'name' => $e->name,
                'slug' => $e->slug,
                'in_catalog' => array_key_exists($e->slug, $catalogBySlug),
            ])->all(),
            'orphans_not_in_catalog' => $orphans->map(fn (Exercise $e): array => [
                'id' => $e->id,
                'name' => $e->name,
                'slug' => $e->slug,
                'equipment' => $e->equipment?->value,
            ])->all(),
            'catalog_not_imported' => $notImported->all(),
        ];

        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Shared DB: %d · Catalog: %d · Missing equipment: %d · Orphans: %d · Not imported: %d',
            $payload['shared_count'],
            $payload['catalog_count'],
            count($payload['missing_equipment']),
            count($payload['orphans_not_in_catalog']),
            count($payload['catalog_not_imported']),
        ));

        if ($payload['missing_equipment'] !== []) {
            $this->newLine();
            $this->warn('Shared exercises with no equipment (plate guide / filters affected):');
            $this->table(
                ['ID', 'Name', 'Slug', 'In catalog?'],
                collect($payload['missing_equipment'])->map(fn (array $row): array => [
                    $row['id'],
                    $row['name'],
                    $row['slug'],
                    $row['in_catalog'] ? 'yes' : 'no',
                ])->all(),
            );
            $this->comment('Fix: add equipment in the catalog (or Admin), then run exercises:import. Orphans need a catalog row with the same slug.');
        }

        if ($payload['orphans_not_in_catalog'] !== []) {
            $this->newLine();
            $this->warn('Shared exercises in DB but not in catalog (predate merge / renamed):');
            $this->table(
                ['ID', 'Name', 'Slug', 'Equipment'],
                collect($payload['orphans_not_in_catalog'])->map(fn (array $row): array => [
                    $row['id'],
                    $row['name'],
                    $row['slug'],
                    $row['equipment'] ?? '—',
                ])->all(),
            );
        }

        if ($payload['catalog_not_imported'] !== []) {
            $this->newLine();
            $this->warn('Catalog rows not yet in DB (run exercises:import):');
            $this->table(
                ['Name', 'Slug', 'Equipment'],
                collect($payload['catalog_not_imported'])->map(fn (array $row): array => [
                    $row['name'],
                    $row['slug'],
                    $row['equipment'] ?? '—',
                ])->take(30)->all(),
            );
            if (count($payload['catalog_not_imported']) > 30) {
                $this->comment(sprintf('…and %d more. Use --json for the full list.', count($payload['catalog_not_imported']) - 30));
            }
        }

        if (
            $payload['missing_equipment'] === []
            && $payload['orphans_not_in_catalog'] === []
            && $payload['catalog_not_imported'] === []
        ) {
            $this->info('Catalog and shared exercises look aligned.');
        }

        return self::SUCCESS;
    }
}
