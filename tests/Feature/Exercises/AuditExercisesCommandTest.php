<?php

namespace Tests\Feature\Exercises;

use App\Exercises\Enums\ExerciseEquipment;
use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\PendingCommand;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditExercisesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_reports_missing_equipment_orphans_and_not_imported(): void
    {
        $group = MuscleGroup::factory()->create(['slug' => 'chest', 'name' => 'Chest']);

        Exercise::factory()->create([
            'name' => 'Legacy Bench',
            'slug' => 'legacy-bench',
            'user_id' => null,
            'primary_muscle_group_id' => $group->id,
            'equipment' => null,
        ]);

        Exercise::factory()->barbell()->create([
            'name' => 'Orphan Press',
            'slug' => 'orphan-press',
            'user_id' => null,
            'primary_muscle_group_id' => $group->id,
        ]);

        $path = storage_path('framework/testing/audit-catalog.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            'muscle_groups' => [
                ['name' => 'Chest', 'slug' => 'chest'],
            ],
            'exercises' => [
                [
                    'name' => 'Catalog Only Press',
                    'slug' => 'catalog-only-press',
                    'primary' => 'chest',
                    'equipment' => 'barbell',
                ],
                [
                    'name' => 'Legacy Bench',
                    'slug' => 'legacy-bench',
                    'primary' => 'chest',
                    'equipment' => 'barbell',
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $exit = Artisan::call('exercises:audit', [
            '--catalog' => $path,
            '--json' => true,
        ]);
        $this->assertSame(0, $exit);

        $output = Artisan::output();
        $this->assertStringContainsString('legacy-bench', $output);
        $this->assertStringContainsString('orphan-press', $output);
        $this->assertStringContainsString('catalog-only-press', $output);

        /** @var array{missing_equipment: list<array{slug: string}>, orphans_not_in_catalog: list<array{slug: string}>, catalog_not_imported: list<array{slug: string}>} $payload */
        $payload = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['legacy-bench'], array_column($payload['missing_equipment'], 'slug'));
        $this->assertSame(['orphan-press'], array_column($payload['orphans_not_in_catalog'], 'slug'));
        $this->assertSame(['catalog-only-press'], array_column($payload['catalog_not_imported'], 'slug'));
    }

    #[Test]
    public function it_confirms_alignment_when_clean(): void
    {
        $group = MuscleGroup::factory()->create(['slug' => 'chest', 'name' => 'Chest']);

        Exercise::factory()->create([
            'name' => 'Bench',
            'slug' => 'bench',
            'user_id' => null,
            'primary_muscle_group_id' => $group->id,
            'equipment' => ExerciseEquipment::Barbell,
        ]);

        $path = storage_path('framework/testing/audit-catalog-clean.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            'muscle_groups' => [
                ['name' => 'Chest', 'slug' => 'chest'],
            ],
            'exercises' => [
                [
                    'name' => 'Bench',
                    'slug' => 'bench',
                    'primary' => 'chest',
                    'equipment' => 'barbell',
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $command = $this->artisan('exercises:audit', ['--catalog' => $path]);
        $this->assertInstanceOf(PendingCommand::class, $command);
        $command->expectsOutputToContain('look aligned')->assertSuccessful()->run();
    }
}
