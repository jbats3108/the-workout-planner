<?php

namespace Tests\Feature\Exercises;

use App\Exercises\Models\Exercise;
use App\Exercises\Services\ExerciseCatalogImporter;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExerciseCatalogImporterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_imports_the_default_shared_catalog(): void
    {
        $result = (new ExerciseCatalogImporter)->importFromPath(ExerciseCatalogImporter::defaultPath());

        $this->assertSame(0, $result['skipped']);
        $this->assertGreaterThan(50, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertGreaterThan(8, MuscleGroup::count());
        $this->assertGreaterThan(50, Exercise::shared()->count());
        $this->assertDatabaseHas('exercises', [
            'slug' => 'barbell-deadlift',
            'user_id' => null,
        ]);
    }

    #[Test]
    public function it_is_idempotent_on_reimport(): void
    {
        $importer = new ExerciseCatalogImporter;
        $importer->importFromPath(ExerciseCatalogImporter::defaultPath());
        $count = Exercise::shared()->count();

        $second = $importer->importFromPath(ExerciseCatalogImporter::defaultPath());

        $this->assertSame(0, $second['created']);
        $this->assertSame($count, $second['updated']);
        $this->assertSame($count, Exercise::shared()->count());
    }

    #[Test]
    public function artisan_command_imports_a_custom_path(): void
    {
        $path = storage_path('framework/testing/catalog.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            'muscle_groups' => [
                ['name' => 'Chest', 'slug' => 'chest'],
            ],
            'exercises' => [
                [
                    'name' => 'Test Press',
                    'slug' => 'test-press',
                    'primary' => 'chest',
                    'secondary' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $this->artisan('exercises:import', ['path' => $path])
            ->assertSuccessful();

        $this->assertDatabaseHas('exercises', [
            'slug' => 'test-press',
            'name' => 'Test Press',
            'user_id' => null,
        ]);
    }
}
