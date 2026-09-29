<?php

namespace Database\Seeders;

use App\ExerciseProfiles\Enums\ExerciseProfileKind;
use App\ExerciseProfiles\Enums\ExerciseProfileStatus;
use App\ExerciseProfiles\Services\ExerciseProfileRecipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ExerciseProfileSeeder extends Seeder
{
    public function run(): void
    {
        $hasDeloadFactors = Schema::hasColumn('exercise_profiles', 'deload_weight_factor');
        $now = now();

        foreach (self::definitions() as $definition) {
            $recipe = new ExerciseProfileRecipe(
                targetReps: $definition['target_reps'],
                floorOverride: $definition['floor_override'],
                workingRestSeconds: $definition['working_rest_seconds'],
                warmUpSteps: $definition['warm_up_steps'],
            );

            $attributes = [
                'user_id' => null,
                'created_by_user_id' => null,
                'kind' => ExerciseProfileKind::Preset->value,
                'status' => ExerciseProfileStatus::Published->value,
                'name' => $definition['name'],
                'target_reps' => $recipe->targetReps,
                'floor_override' => $recipe->floorOverride,
                'working_rest_seconds' => $recipe->workingRestSeconds,
                'warm_up_steps' => json_encode($recipe->warmUpSteps),
                'recipe_fingerprint' => $recipe->fingerprint(),
                'published_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasDeloadFactors) {
                $attributes['deload_weight_factor'] = $definition['deload_weight_factor'];
                $attributes['deload_reps_factor'] = $definition['deload_reps_factor'];
            }

            $existing = DB::table('exercise_profiles')
                ->where('slug_scope', 'system')
                ->where('slug', $definition['slug'])
                ->first();

            if ($existing !== null) {
                DB::table('exercise_profiles')
                    ->where('id', $existing->id)
                    ->update($attributes);

                continue;
            }

            DB::table('exercise_profiles')->insert([
                ...$attributes,
                'slug_scope' => 'system',
                'slug' => $definition['slug'],
                'created_at' => $now,
            ]);
        }
    }

    public static function defaultPath(): string
    {
        return database_path('data/exercise-profile-presets.json');
    }

    /**
     * @return list<array{
     *     name: string,
     *     slug: string,
     *     target_reps: int,
     *     floor_override: int|null,
     *     working_rest_seconds: int,
     *     deload_weight_factor: float,
     *     deload_reps_factor: float,
     *     warm_up_steps: list<array{mode?: string, percent?: int, reps: int}>
     * }>
     */
    public static function definitions(): array
    {
        $path = self::defaultPath();
        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Preset catalog is not valid JSON: {$path}");
        }

        /** @var list<array{
         *     name: string,
         *     slug: string,
         *     target_reps: int,
         *     floor_override: int|null,
         *     working_rest_seconds: int,
         *     deload_weight_factor: float,
         *     deload_reps_factor: float,
         *     warm_up_steps: list<array{mode?: string, percent?: int, reps: int}>
         * }> $decoded
         */
        return $decoded;
    }
}
