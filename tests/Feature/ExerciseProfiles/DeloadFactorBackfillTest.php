<?php

namespace Tests\Feature\ExerciseProfiles;

use App\ExerciseProfiles\Enums\ExerciseProfileKind;
use App\ExerciseProfiles\Models\ExerciseProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeloadFactorBackfillTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function published_presets_have_grill_deload_factors(): void
    {
        $strength = ExerciseProfile::query()->where('slug', 'preset-strength')->firstOrFail();
        $hypertrophy = ExerciseProfile::query()->where('slug', 'preset-hypertrophy')->firstOrFail();
        $endurance = ExerciseProfile::query()->where('slug', 'preset-endurance')->firstOrFail();

        $this->assertSame(ExerciseProfileKind::Preset, $strength->kind);
        $this->assertEqualsWithDelta(0.5, (float) $strength->deload_weight_factor, 0.0001);
        $this->assertEqualsWithDelta(2.0, (float) $strength->deload_reps_factor, 0.0001);

        $this->assertSame(ExerciseProfileKind::Preset, $hypertrophy->kind);
        $this->assertEqualsWithDelta(0.5, (float) $hypertrophy->deload_weight_factor, 0.0001);
        $this->assertEqualsWithDelta(1.5, (float) $hypertrophy->deload_reps_factor, 0.0001);

        $this->assertSame(ExerciseProfileKind::Preset, $endurance->kind);
        $this->assertEqualsWithDelta(0.5, (float) $endurance->deload_weight_factor, 0.0001);
        $this->assertEqualsWithDelta(1.0, (float) $endurance->deload_reps_factor, 0.0001);
    }
}
