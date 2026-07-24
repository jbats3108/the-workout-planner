<?php

namespace App\Exercises\Services;

use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use InvalidArgumentException;
use RuntimeException;

class ExerciseCatalogImporter
{
    /**
     * @return array{muscle_groups: int, created: int, updated: int, skipped: int}
     */
    public function importFromPath(string $path): array
    {
        if (! is_readable($path)) {
            throw new InvalidArgumentException("Catalog file is not readable: {$path}");
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Catalog file is not valid JSON: {$path}");
        }

        return $this->import($decoded);
    }

    /**
     * @param  array{muscle_groups?: list<array{name: string, slug: string}>, exercises?: list<array{name: string, slug: string, primary: string, secondary?: ?string}>}  $catalog
     * @return array{muscle_groups: int, created: int, updated: int, skipped: int}
     */
    public function import(array $catalog): array
    {
        $groupsCreated = 0;
        $groupIdsBySlug = [];

        foreach ($catalog['muscle_groups'] ?? [] as $group) {
            $slug = $group['slug'] ?? null;
            $name = $group['name'] ?? null;
            if (! is_string($slug) || $slug === '' || ! is_string($name) || $name === '') {
                continue;
            }

            $model = MuscleGroup::withTrashed()->firstOrNew(['slug' => $slug]);
            if (! $model->exists) {
                $groupsCreated++;
            }
            $model->name = $name;
            if ($model->trashed()) {
                $model->restore();
            }
            $model->save();
            $groupIdsBySlug[$slug] = $model->id;
        }

        // Ensure lookups work for groups that already existed but weren't in this file.
        foreach (MuscleGroup::query()->get(['id', 'slug']) as $existing) {
            $groupIdsBySlug[$existing->slug] ??= $existing->id;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($catalog['exercises'] ?? [] as $row) {
            $slug = $row['slug'] ?? null;
            $name = $row['name'] ?? null;
            $primarySlug = $row['primary'] ?? null;
            $secondarySlug = $row['secondary'] ?? null;

            if (! is_string($slug) || $slug === '' || ! is_string($name) || $name === '' || ! is_string($primarySlug)) {
                $skipped++;

                continue;
            }

            $primaryId = $groupIdsBySlug[$primarySlug] ?? null;
            if ($primaryId === null) {
                $skipped++;

                continue;
            }

            $secondaryId = null;
            if (is_string($secondarySlug) && $secondarySlug !== '') {
                $secondaryId = $groupIdsBySlug[$secondarySlug] ?? null;
                if ($secondaryId === null) {
                    $skipped++;

                    continue;
                }
            }

            $exercise = Exercise::withTrashed()->firstOrNew([
                'slug' => $slug,
                'user_id' => null,
            ]);

            $isNew = ! $exercise->exists;
            $exercise->name = $name;
            $exercise->primary_muscle_group_id = $primaryId;
            $exercise->secondary_muscle_group_id = $secondaryId;
            if ($exercise->trashed()) {
                $exercise->restore();
            }
            $exercise->save();

            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }
        }

        return [
            'muscle_groups' => $groupsCreated,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    public static function defaultPath(): string
    {
        return database_path('data/exercises.json');
    }
}
