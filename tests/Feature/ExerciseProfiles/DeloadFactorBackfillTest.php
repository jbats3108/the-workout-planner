<?php

namespace Tests\Feature\ExerciseProfiles;

use App\ExerciseProfiles\Enums\ExerciseProfileKind;
use App\ExerciseProfiles\Models\ExerciseProfile;
use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeloadFactorBackfillTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function backfill_copies_preset_factors_and_falls_back_to_routine_factors(): void
    {
        $this->assertTrue(Schema::hasColumn('exercise_profiles', 'deload_weight_factor'));
        $this->assertTrue(Schema::hasColumn('routine_block_exercises', 'deload_reps_factor'));

        $user = User::factory()->create();
        $strength = ExerciseProfile::query()->where('slug', 'preset-strength')->firstOrFail();
        $this->assertSame(ExerciseProfileKind::Preset, $strength->kind);
        $this->assertEqualsWithDelta(0.5, (float) $strength->deload_weight_factor, 0.0001);
        $this->assertEqualsWithDelta(2.0, (float) $strength->deload_reps_factor, 0.0001);

        $custom = ExerciseProfile::factory()->forUser($user)->create([
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 0.5,
        ]);
        $exercise = Exercise::factory()->create();

        $routine = Routine::factory()->for($user)->create([
            'deload_weight_factor' => 0.7,
            'deload_reps_factor' => 1.25,
        ]);
        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);

        $onPreset = RoutineBlockExercise::query()->create([
            'routine_block_id' => $block->id,
            'exercise_profile_id' => $strength->id,
            'exercise_id' => $exercise->id,
            'position' => 1,
            'working_weight_g' => 100_000,
            'prescribed_reps' => 5,
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 0.5,
        ]);
        $onCustom = RoutineBlockExercise::query()->create([
            'routine_block_id' => $block->id,
            'exercise_profile_id' => $custom->id,
            'exercise_id' => $exercise->id,
            'position' => 2,
            'working_weight_g' => 80_000,
            'prescribed_reps' => 10,
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 0.5,
        ]);

        /** @var object{up(): void} $migration */
        $migration = require database_path(
            'migrations/2026_09_29_082457_backfill_deload_factors_onto_profiles_and_routine_block_exercises.php'
        );
        $migration->up();

        $onPreset->refresh();
        $onCustom->refresh();
        $this->assertEqualsWithDelta(0.5, (float) $onPreset->deload_weight_factor, 0.0001);
        $this->assertEqualsWithDelta(2.0, (float) $onPreset->deload_reps_factor, 0.0001);
        $this->assertEqualsWithDelta(0.7, (float) $onCustom->deload_weight_factor, 0.0001);
        $this->assertEqualsWithDelta(1.25, (float) $onCustom->deload_reps_factor, 0.0001);
    }
}
